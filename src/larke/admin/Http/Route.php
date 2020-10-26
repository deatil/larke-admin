<?php

namespace Larke\Admin\Http;

use Illuminate\Support\Facades\Route as RouteSupport;

/*
 * Route
 *
 * @create 2020-10-26
 * @author deatil
 */
class Route
{
    /**
     * Set routes for this Route.
     *
     * @param $callback
     */
    public static function routes($callback, $config = [])
    {
        $attributes = array_merge(
            [
                'prefix' => config('larke.route.prefix'),
                'middleware' => config('larke.route.middleware'),
            ],
            $config
        );

        RouteSupport::group($attributes, $callback);
    }
}
