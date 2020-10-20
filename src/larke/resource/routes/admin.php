<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('larke.route.prefix'),
], function ($router) {
    $router->namespace(config('larke.route.namespace'))->group(function ($router) {
        
        $router->get('/', 'Index@index')->name('larke-admin-index');
        
        $router->post('/passport/login', 'Passport@login')->name('larke-admin-login');

    });
});
