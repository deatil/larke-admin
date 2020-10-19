<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => env('LARKE_ADMIN_ROUTE_PREFIX', 'admin'),
], function ($router) {
    $router->namespace('Larke\\Admin\\Controller')->group(function ($router) {
        
        $router->get('/', 'Index@index')->name('larke-admin-index');

    });
});
