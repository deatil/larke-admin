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
            
            foreach ($route['method'] as $method) {
                $ruleInfo = AuthRuleModel::where('slug', $route['name'])
                    ->where('method', $method)
                    ->first();
                if (!empty($ruleInfo)) {
                    $data = [
                        'url' => $route['uri'],
                        'update_time' => time(),
                        'update_ip' => request()->ip(),
                    ];
                    AuthRuleModel::where('id', $ruleInfo['id'])
                        ->update($data);
                } else {
                    $data = [
                        'id' => md5(mt_rand(10000, 99999).time().mt_rand(10000, 99999)),
                        'parentid' => 0,
                        'slug' => $route['name'],
                        'url' => $route['uri'],
                        'method' => $method,
                        'description' => $route['name'],
                        'listorder' => 100,
                        'is_need_auth' => 1,
                        'is_system' => 0,
                        'status' => 1,
                        'update_time' => time(),
                        'update_ip' => request()->ip(),
                        'create_time' => time(),
                        'create_ip' => request()->ip(),
                    ];
                    
                    AuthRuleModel::create($data);
                }
            }
        }
    }
}
