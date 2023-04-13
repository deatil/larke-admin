<?php

use Larke\Admin\Facade\Extension;

Extension::routes(function ($router) {
    $router->namespace('App\\Admin\\Http\\Controllers')
        ->group(function ($router) {
            $router->get('/', 'HomeController@index')->name('app-admin.home');
        });
});
