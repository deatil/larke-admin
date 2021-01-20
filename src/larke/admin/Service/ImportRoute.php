<?php

declare (strict_types = 1);

namespace Larke\Admin\Service;

use ReflectionClass;

use Illuminate\Support\Arr;

use Larke\Admin\Support\Doc;
use Larke\Admin\Service\Route as RouteService;
use Larke\Admin\Model\AuthRule as AuthRuleModel;

/**
 * 导入路由信息
 *
 * @create 2021-1-10
 * @author deatil
 */
class ImportRoute
{
    /**
     * 导入
     */
    public function import()
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
    public function importClosureRoute($route)
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
    public function importActionRoute($route)
    {
        // 路由类信息
        $classDoc = $this->formatActionClassDoc($route['action']);
        $classDocInfo = [
            'slug' => '',
            'title' => '',
            'description' => '',
            'listorder' => 100,
            'is_need_auth' => 1,
        ];
        $classDocInfo = array_merge($classDocInfo, $classDoc);
        $classParent = Arr::pull($classDocInfo, 'parent');
        
        // 路由方法信息
        $methodDoc = $this->formatActionMethodDoc($route['action']);
        $methodDocInfo = [
            'slug' => '',
            'title' => '',
            'description' => '',
            'listorder' => 100,
            'is_need_auth' => 1,
        ];
        $methodDocInfo = array_merge($methodDocInfo, $methodDoc);
        $methodParent = Arr::pull($methodDocInfo, 'parent');
        
        // 父级slug判断
        if (! empty($classDocInfo['slug'])) {
            if (empty($classDocInfo['title'])) {
                $classDocInfo['title'] = $classDocInfo['slug'];
            }
            if (empty($classDocInfo['description'])) {
                $classDocInfo['description'] = $classDocInfo['slug'];
            }
        } elseif (! empty($methodParent)) {
            $classDocInfo['slug'] = $methodParent;
            $classDocInfo['title'] = $methodParent;
            $classDocInfo['description'] = $methodParent;
        } else {
            $classDocInfo['slug'] = '';
            $classDocInfo['title'] = '';
            $classDocInfo['description'] = '';
        }
        
        // 设置父级slug
        if (! empty($classDocInfo['slug'])) {
            $oldParent = AuthRuleModel::where('slug', $classDocInfo['slug'])
                ->first();
            if (! empty($oldParent)) {
                $parentid = $oldParent->id;
            } else {
                $parentData = array_merge([
                    'parentid' => 0,
                    'url' => '#',
                    'method' => 'OPTIONS',
                    'slug' => '',
                    'is_system' => 0,
                    'status' => 1,
                ], $classDocInfo);
                
                $parent = AuthRuleModel::create($parentData);
                
                $parentid = $parent->id;
            }
        } else {
            $parentid = 0;
        }
        
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
    public function formatAction($actions)
    {
        if (empty($actions)) {
            return false;
        }
        
        if (! is_array($actions)) {
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
    public function formatActionClassDoc($action)
    {
        $actions = $this->formatAction($action);
        if ($actions === false) {
            return false;
        }
        
        list ($actionClass, $actionMethod) = $actions;
        
        $reflection = new ReflectionClass($actionClass);
        $docComment = $this->parseDoc($reflection->getDocComment());
        
        $commentInfo = [
            'parent' => Arr::get($docComment, 'parent'),
            'slug' => Arr::get($docComment, 'slug'),
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
    public function formatActionMethodDoc($action)
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
            'parent' => Arr::get($docComment, 'parent'),
            'slug' => Arr::get($docComment, 'slug'),
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
    public function parseDoc($text)
    {
        $doc = new Doc();
        return $doc->parse($text);
    }
    
}
