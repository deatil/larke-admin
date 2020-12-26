<?php

namespace Larke\Admin\Service;

use Illuminate\Routing\Route as Router;

/**
 * 路由
 */
class Route
{
    /**
     * 获取路由信息
     *
     * @return mixed
     */
    public function getRoutes()
    {
        $routes = app('router')->getRoutes();
        $routes = collect($routes)->map(function ($route) {
            return $this->getRouteInformation($route);
        })->all();

        return $routes;
    }
    
    protected function getRouteInformation(Router $route)
    {
        return [
            'host'       => $route->domain(),
            'method'     => $route->methods(),
            'uri'        => $route->uri(),
            'prefix'     => $route->getPrefix(),
            'name'       => $route->getName(),
            'action'     => $route->getActionName(),
            'middleware' => $this->getRouteMiddleware($route),
        ];
    }
    
    /**
     * Get before filters.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return string
     */
    protected function getRouteMiddleware($route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof \Closure ? 'Closure' : $middleware;
        });
    }
    
    /**
     * 格式化路由标识
     */
    public static function formatRouteSlug($slug = '')
    {
        if (empty($slug)) {
            return '';
        }
        
        $newSlug = '';
        
        $routeAs = config('larkeadmin.route.as', '');
        if (! empty($routeAs)) {
            $newSlug = sprintf('%s'.$slug, $routeAs);
        }
        
        return $newSlug;
    }
    
}
