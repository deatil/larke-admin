<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('larke.route.prefix'),
    'middleware' => config('larke.route.middleware'),
    'namespace' => config('larke.route.namespace'),
    'as' => 'larke-admin-',
], function ($router) {
    $router->group([
        'middleware' => config('larke.route.admin_middleware'),
    ], function ($router) {
        $router->get('/auth/rule/index', 'AuthRule@index')->name('auth-rule-index');
        $router->get('/auth/rule/index-tree', 'AuthRule@indexTree')->name('auth-rule-index-tree');
        $router->get('/auth/rule/index-children', 'AuthRule@indexChildren')->name('auth-rule-index-children');
        $router->get('/auth/rule/{id}', 'AuthRule@detail')->name('auth-rule-detail');
        $router->post('/auth/rule/create', 'AuthRule@create')->name('auth-rule-create');
        $router->put('/auth/rule/{id}', 'AuthRule@update')->name('auth-rule-update');
        $router->patch('/auth/rule/listorder/{id}', 'AuthRule@listorder')->name('auth-rule-listorder');
        $router->patch('/auth/rule/enable/{id}', 'AuthRule@enable')->name('auth-rule-enable');
        $router->patch('/auth/rule/disable/{id}', 'AuthRule@disable')->name('auth-rule-disable');
        $router->delete('/auth/rule/{id}', 'AuthRule@delete')->name('auth-rule-delete');
        
        $router->get('/auth/group/index', 'AuthGroup@index')->name('auth-group-index');
        $router->get('/auth/group/index-tree', 'AuthGroup@indexTree')->name('auth-group-index-tree');
        $router->get('/auth/group/index-children', 'AuthGroup@indexChildren')->name('auth-group-index-children');
        $router->get('/auth/group/{id}', 'AuthGroup@detail')->name('auth-group-detail');
        $router->post('/auth/group/create', 'AuthGroup@create')->name('auth-group-create');
        $router->put('/auth/group/{id}', 'AuthGroup@update')->name('auth-group-update');
        $router->patch('/auth/group/listorder/{id}', 'AuthGroup@listorder')->name('auth-group-listorder');
        $router->patch('/auth/group/enable/{id}', 'AuthGroup@enable')->name('auth-group-enable');
        $router->patch('/auth/group/disable/{id}', 'AuthGroup@disable')->name('auth-group-disable');
        $router->delete('/auth/group/{id}', 'AuthGroup@delete')->name('auth-group-delete');
        $router->put('/auth/group/access/{id}', 'AuthGroup@access')->name('auth-group-access');
    });
    
    $router->get('/passport/captcha', 'Passport@captcha')->name('passport-captcha');
    $router->post('/passport/login', 'Passport@login')->name('passport-login');
    $router->post('/passport/logout', 'Passport@logout')->name('passport-logout');
    $router->put('/passport/refresh-token', 'Passport@refreshToken')->name('passport-refresh-token');
    
    $router->get('/profile', 'Profile@index')->name('profile');
    $router->put('/profile/update', 'Profile@update')->name('profile-update');
    $router->put('/profile/password', 'Profile@changePasssword')->name('profile-password');
    $router->get('/profile/rules', 'Profile@rules')->name('profile-rules');
    
    $router->get('/attachment/index', 'Attachment@index')->name('attachment-index');
    $router->get('/attachment/{id}', 'Attachment@detail')->name('attachment-detail');
    $router->patch('/attachment/enable/{id}', 'Attachment@enable')->name('attachment-enable');
    $router->patch('/attachment/disable/{id}', 'Attachment@disable')->name('attachment-disable');
    $router->delete('/attachment/{id}', 'Attachment@delete')->name('attachment-delete');
    $router->post('/attachment/upload', 'Attachment@upload')->name('attachment-upload');
    $router->get('/attachment/downcode/{id}', 'Attachment@downloadCode')->name('attachment-download-code');
    $router->get('/attachment/download/{code}', 'Attachment@download')->name('attachment-download');
    
    $router->get('/admin/index', 'Admin@index')->name('admin-index');
    $router->get('/admin/{id}', 'Admin@detail')->name('admin-detail');
    $router->get('/admin/rules/{id}', 'Admin@rules')->name('admin-rules');
    $router->post('/admin/create', 'Admin@create')->name('admin-create');
    $router->put('/admin/{id}', 'Admin@update')->name('admin-update');
    $router->patch('/admin/enable/{id}', 'Admin@enable')->name('admin-enable');
    $router->patch('/admin/disable/{id}', 'Admin@disable')->name('admin-disable');
    $router->delete('/admin/{id}', 'Admin@delete')->name('admin-delete');
    $router->put('/admin/password/{id}', 'Admin@changePasssword')->name('admin-password');
    $router->put('/admin/access/{id}', 'Admin@access')->name('admin-access');
    $router->post('/admin/logout/{refreshToken}', 'Admin@logout')->name('admin-logout');
    
    $router->get('/config/index', 'Config@index')->name('config-index');
    $router->get('/config/{id}', 'Config@detail')->name('config-detail');
    $router->post('/config/create', 'Config@create')->name('config-create');
    $router->put('/config/{id}', 'Config@update')->name('config-update');
    $router->patch('/config/listorder/{id}', 'Config@listorder')->name('config-listorder');
    $router->patch('/config/enable/{id}', 'Config@enable')->name('config-enable');
    $router->patch('/config/disable/{id}', 'Config@disable')->name('config-disable');
    $router->delete('/config/{id}', 'Config@delete')->name('config-delete');
    $router->put('/config/setting', 'Config@setting')->name('config-setting');
    
    $router->get('/log/index', 'Log@index')->name('log-index');
    $router->get('/log/{id}', 'Log@detail')->name('log-detail');
    $router->delete('/log/{id}', 'Log@delete')->name('log-delete');
    
    $router->get('/extension/index', 'Extension@index')->name('extension-index');
    $router->get('/extension/local', 'Extension@local')->name('extension-local');
    $router->put('/extension/config/{name}', 'Extension@config')->name('extension-config');
    $router->post('/extension/install/{name}', 'Extension@install')->name('extension-install');
    $router->delete('/extension/uninstall/{name}', 'Extension@uninstall')->name('extension-uninstall');
    $router->put('/extension/upgrade/{name}', 'Extension@upgrade')->name('extension-upgrade');
    $router->patch('/extension/enable/{name}', 'Extension@enable')->name('extension-enable');
    $router->patch('/extension/disable/{name}', 'Extension@disable')->name('extension-disable');
    $router->patch('/extension/listorder/{name}', 'Extension@listorder')->name('extension-listorder');
    $router->post('/extension/upload', 'Extension@upload')->name('extension-upload');
    
    $router->get('/system/info', 'System@info')->name('system-info');
    $router->get('/system/lang', 'System@lang')->name('system-lang');
    $router->post('/system/cache', 'System@cache')->name('system-cache');
    $router->post('/system/clear-cache', 'System@clearCache')->name('system-clear-cache');
});
