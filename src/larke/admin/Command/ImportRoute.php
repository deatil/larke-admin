<?php

namespace Larke\Admin\Command;

use Illuminate\Console\Command;

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
        $this->import();
        
        $this->info('import route success!');
    }
    
    /**
     * 导入
     * 
     * @create 2020-9-14
     * @author deatil
     */
    protected function import()
    {
        $RouteService = (new RouteService);
        $routes = $RouteService->getRoutes();
        if (empty($routes)) {
            return false;
        }
        
        foreach ($routes as $route) {
            if (!isset($route['prefix']) 
                || empty($route['method'])
                || empty($route['name'])
                || $route['prefix'] != config('larke.route.prefix')
            ) {
                continue;
            }
            
            if (!empty($route['action']) 
                && strpos($route['action'], '@') !== false
            ) {
                $parentRoute = explode('@', $route['action']);
                
                $oldParent = AuthRuleModel::where('title', $parentRoute[0])
                    ->first();
                if (!empty($oldParent)) {
                    $parentid = $oldParent->id;
                } else {
                    $parent = AuthRuleModel::create([
                        'parentid' => 0,
                        'title' => $parentRoute[0],
                        'url' => '',
                        'method' => '',
                        'slug' => '',
                        'description' => $route['action'],
                        'listorder' => 100,
                        'is_need_auth' => 0,
                        'is_system' => 0,
                        'status' => 1,
                    ]);
                    
                    $parentid = $parent->id;
                }
            } else {
                $parentid = 0;
            }
            
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
                        'parentid' => $parentid,
                        'title' => $route['name'],
                        'url' => $route['uri'],
                        'method' => $method,
                        'slug' => $route['name'],
                        'description' => $route['name'],
                        'listorder' => 100,
                        'is_need_auth' => 1,
                        'is_system' => 0,
                        'status' => 1,
                    ]);
                }
            }
        }
    }
}
