<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('larke.route.prefix'),
], function ($router) {
    $router->namespace(config('larke.route.namespace'))->group(function ($router) {
        $router->get('index', 'Index@index')->name('larke-admin-index');
        $router->get('menu', 'Menu@index')->name('larke-admin-menu-index');
        $router->post('menu', 'Menu@store')->name('larke-admin-menu-post');
    });
});
