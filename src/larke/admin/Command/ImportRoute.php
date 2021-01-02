<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use ReflectionClass;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;

use Larke\Admin\Support\Doc;
use Larke\Admin\Service\Route as RouteService;
use Larke\Admin\Model\AuthRule as AuthRuleModel;

/**
 * 导入路由信息
 *
 * php artisan larke-admin:import-route
 *
 */
class ImportRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:import-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin import route\'info.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->import();
        } catch(\Exception $e) {
            $this->line("<error>{$e->getMessage()}!</error> ");
            return;
        }
        
        $this->info('Import route successfully!');
    }
    
    /**
     * 导入
     */
    protected function import()
    {
        $RouteService = (new RouteService);
        $routes = $RouteService->getRoutes();
        if (empty($routes)) {
            return false;
        }
        
        foreach ($routes as $route) {
            if (! isset($route['prefix']) 
                || empty($route['method'])
                || empty($route['name'])
                || $route['prefix'] != config('larkeadmin.route.prefix')
            ) {
                continue;
            }
            
            $route['uri'] = substr($route['uri'], strlen($route['prefix']) + 1);
            
            $actions = $this->formatAction($route['action']);
            if ($actions !== false) {
                $this->importActionRoute($route);
            } else {
                $this->importClosureRoute($route);
            }
            
        }
    }
    
    /**
     * 导入匿名函数路由
     */
    protected function importClosureRoute($route)
    {
        foreach ($route['method'] as $method) {
            $ruleInfo = AuthRuleModel::where('slug', $route['name'])
                ->where('method', $method)
                ->first();
            if (!empty($ruleInfo)) {
                $ruleInfo->update([
                        'url' => $route['uri'],
                    ]);
            } else {
                AuthRuleModel::create([
                    'parentid' => 0,
                    'title' => $route['name'],
                    'description' => $route['name'],
                    'url' => $route['uri'],
                    'method' => $method,
                    'slug' => $route['name'],
                    'listorder' => 100,
                    'is_need_auth' => 1,
                    'is_system' => 0,
                    'status' => 1,
                ]);
            }
        }
    }
    
    /**
     * 导入常规路由
     */
    protected function importActionRoute($route)
    {
        $classDoc = $this->formatActionClassDoc($route['action']);
        $classDocInfo = [
            'title' => '',
            'description' => '',
            'listorder' => 100,
            'is_need_auth' => 1,
        ];
        $classDocInfo = array_merge($classDocInfo, $classDoc);
        
        $actions = $this->formatAction($route['action']);
        list ($actionClass, $actionMethod) = $actions;
        
        $oldParent = AuthRuleModel::where('slug', md5($actionClass))
            ->first();
        if (!empty($oldParent)) {
            $parentid = $oldParent->id;
        } else {
            if (empty($classDocInfo['title'])) {
                $classDocInfo['title'] = $route['name'];
            }
            if (empty($classDocInfo['description'])) {
                $classDocInfo['description'] = $route['name'];
            }
            
            $parentData = array_merge($classDocInfo, [
                'parentid' => 0,
                'url' => '#',
                'method' => 'OPTIONS',
                'slug' => md5($actionClass),
                'is_system' => 0,
                'status' => 1,
            ]);
            
            $parent = AuthRuleModel::create($parentData);
            
            $parentid = $parent->id;
        }
        
        $methodDoc = $this->formatActionMethodDoc($route['action']);
        $methodDocInfo = [
            'title' => '',
            'description' => '',
            'listorder' => 100,
            'is_need_auth' => 1,
        ];
        $methodDocInfo = array_merge($methodDocInfo, $methodDoc);
        
        if (empty($methodDocInfo['title'])) {
            $methodDocInfo['title'] = $route['name'];
        }
        if (empty($methodDocInfo['description'])) {
            $methodDocInfo['description'] = $route['name'];
        }
        
        foreach ($route['method'] as $method) {
            $ruleInfo = AuthRuleModel::where('slug', $route['name'])
                ->where('method', $method)
                ->first();
            if (!empty($ruleInfo)) {
                $data = array_merge($methodDocInfo, [
                        'url' => $route['uri'],
                    ]);
                $ruleInfo->update($data);
            } else {
                $data = array_merge($methodDocInfo, [
                    'parentid' => $parentid,
                    'url' => $route['uri'],
                    'method' => $method,
                    'slug' => $route['name'],
                    'is_system' => 0,
                    'status' => 1,
                ]);
                
                AuthRuleModel::create($data);
            }
        }
    }
    
    /**
     * 格式化
     */
    protected function formatAction($actions)
    {
        if (empty($actions)) {
            return false;
        }
        
        if (!is_array($actions)) {
            $actions = explode('@', $actions);
            if (count($actions) < 2) {
                return false;
            }
        }
        
        list ($actionClass, $actionMethod) = $actions;
        
        return [$actionClass, $actionMethod];
    }
    
    /**
     * 格式化类注释
     */
    protected function formatActionClassDoc($action)
    {
        $actions = $this->formatAction($action);
        if ($actions === false) {
            return false;
        }
        
        list ($actionClass, $actionMethod) = $actions;
        
        $reflection = new ReflectionClass($actionClass);
        $docComment = $this->parseDoc($reflection->getDocComment());
        
        $commentInfo = [
            'title' => Arr::get($docComment, 'title'),
            'description' => Arr::get($docComment, 'desc'),
            'listorder' => Arr::get($docComment, 'order', 100),
            'is_need_auth' => Arr::get($docComment, 'auth', 'true'),
        ];
        
        if ($commentInfo['is_need_auth'] === 'true') {
            $commentInfo['is_need_auth'] = 1;
        } else {
            $commentInfo['is_need_auth'] = 0;
        }
        
        return $commentInfo;
        
    }
    
    /**
     * 格式化方法注释
     */
    protected function formatActionMethodDoc($action)
    {
        $actions = $this->formatAction($action);
        if ($actions === false) {
            return false;
        }
        
        list ($actionClass, $actionMethod) = $actions;
        
        $reflection = new ReflectionClass($actionClass);
        $methodDocComment = $reflection->getMethod($actionMethod)->getDocComment();
        $docComment = $this->parseDoc($methodDocComment);
        
        $commentInfo = [
            'title' => Arr::get($docComment, 'title'),
            'description' => Arr::get($docComment, 'desc'),
            'listorder' => Arr::get($docComment, 'order', 100),
            'is_need_auth' => Arr::get($docComment, 'auth', 'true'),
        ];
        
        if ($commentInfo['is_need_auth'] === 'true') {
            $commentInfo['is_need_auth'] = 1;
        } else {
            $commentInfo['is_need_auth'] = 0;
        }
        
        return $commentInfo;
    }
    
    /**
     * 解析注释
     */
    protected function parseDoc($text)
    {
        $doc = new Doc();
        return $doc->parse($text);
    }
    
}
