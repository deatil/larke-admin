<?php

use Larke\Admin\Facade\Extension;

Extension::routes(function ($router) {
    $router
        ->namespace('{composerNamespace}\\Controller')
        ->group(function ($router) {
            // {extensionName} 路由
            $router->get('/{extensionName}', 'Index@index')
                ->name('larke-admin.{extensionName}.index');
            
            $router->get('/{extensionName}/{id}', 'Index@detail')
                ->name('larke-admin.{extensionName}.detail')
                ->where('id', '[A-Za-z0-9]+');
            
            $router->delete('/{extensionName}/{id}', 'Index@delete')
                ->name('larke-admin.{extensionName}.delete')
                ->where('id', '[A-Za-z0-9]+');
        });
});