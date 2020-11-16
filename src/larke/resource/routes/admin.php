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
        $router->get('/auth/rule', 'AuthRule@index')->name('auth-rule-index');
        $router->get('/auth/rule/tree', 'AuthRule@indexTree')->name('auth-rule-index-tree');
        $router->get('/auth/rule/children', 'AuthRule@indexChildren')->name('auth-rule-index-children');
        $router->get('/auth/rule/{id}', 'AuthRule@detail')->name('auth-rule-detail');
        $router->post('/auth/rule', 'AuthRule@create')->name('auth-rule-create');
        $router->put('/auth/rule/{id}', 'AuthRule@update')->name('auth-rule-update');
        $router->delete('/auth/rule/{id}', 'AuthRule@delete')->name('auth-rule-delete');
        $router->patch('/auth/rule/{id}/sort', 'AuthRule@listorder')->name('auth-rule-listorder');
        $router->patch('/auth/rule/{id}/enable', 'AuthRule@enable')->name('auth-rule-enable');
        $router->patch('/auth/rule/{id}/disable', 'AuthRule@disable')->name('auth-rule-disable');
        
        $router->get('/auth/group', 'AuthGroup@index')->name('auth-group-index');
        $router->get('/auth/group/tree', 'AuthGroup@indexTree')->name('auth-group-index-tree');
        $router->get('/auth/group/children', 'AuthGroup@indexChildren')->name('auth-group-index-children');
        $router->get('/auth/group/{id}', 'AuthGroup@detail')->name('auth-group-detail');
        $router->post('/auth/group', 'AuthGroup@create')->name('auth-group-create');
        $router->put('/auth/group/{id}', 'AuthGroup@update')->name('auth-group-update');
        $router->delete('/auth/group/{id}', 'AuthGroup@delete')->name('auth-group-delete');
        $router->patch('/auth/group/{id}/sort', 'AuthGroup@listorder')->name('auth-group-listorder');
        $router->patch('/auth/group/{id}/enable', 'AuthGroup@enable')->name('auth-group-enable');
        $router->patch('/auth/group/{id}/disable', 'AuthGroup@disable')->name('auth-group-disable');
        $router->put('/auth/group/{id}/access', 'AuthGroup@access')->name('auth-group-access');
    });
    
    $router->get('/passport/captcha', 'Passport@captcha')->name('passport-captcha');
    $router->post('/passport/login', 'Passport@login')->name('passport-login');
    $router->delete('/passport/logout', 'Passport@logout')->name('passport-logout');
    $router->put('/passport/refresh-token', 'Passport@refreshToken')->name('passport-refresh-token');
    
    $router->get('/profile', 'Profile@index')->name('profile');
    $router->put('/profile/update', 'Profile@update')->name('profile-update');
    $router->put('/profile/avatar', 'Profile@updateAvatar')->name('profile-avatar');
    $router->put('/profile/password', 'Profile@updatePasssword')->name('profile-password');
    $router->get('/profile/rules', 'Profile@rules')->name('profile-rules');
    
    $router->get('/attachment', 'Attachment@index')->name('attachment-index');
    $router->get('/attachment/{id}', 'Attachment@detail')->name('attachment-detail');
    $router->patch('/attachment/{id}/enable', 'Attachment@enable')->name('attachment-enable');
    $router->patch('/attachment/{id}/disable', 'Attachment@disable')->name('attachment-disable');
    $router->delete('/attachment/{id}', 'Attachment@delete')->name('attachment-delete');
    $router->post('/attachment', 'Attachment@upload')->name('attachment-upload');
    $router->get('/attachment/downcode/{id}', 'Attachment@downloadCode')->name('attachment-download-code');
    $router->get('/attachment/download/{code}', 'Attachment@download')->name('attachment-download');
    
    $router->get('/admin', 'Admin@index')->name('admin-index');
    $router->get('/admin/{id}', 'Admin@detail')->name('admin-detail');
    $router->get('/admin/{id}/rules', 'Admin@rules')->name('admin-rules');
    $router->post('/admin', 'Admin@create')->name('admin-create');
    $router->put('/admin/{id}', 'Admin@update')->name('admin-update');
    $router->delete('/admin/{id}', 'Admin@delete')->name('admin-delete');
    $router->patch('/admin/{id}/enable', 'Admin@enable')->name('admin-enable');
    $router->patch('/admin/{id}/disable', 'Admin@disable')->name('admin-disable');
    $router->put('/admin/{id}/avatar', 'Admin@updateAvatar')->name('admin-avatar');
    $router->put('/admin/{id}/password', 'Admin@updatePasssword')->name('admin-password');
    $router->put('/admin/{id}/access', 'Admin@access')->name('admin-access');
    $router->delete('/admin/logout/{refreshToken}', 'Admin@logout')->name('admin-logout');
    
    $router->get('/config', 'Config@index')->name('config-index');
    $router->get('/config/{id}', 'Config@detail')->name('config-detail');
    $router->post('/config', 'Config@create')->name('config-create');
    $router->put('/config/{id}', 'Config@update')->name('config-update');
    $router->delete('/config/{id}', 'Config@delete')->name('config-delete');
    $router->patch('/config/{id}/sort', 'Config@listorder')->name('config-listorder');
    $router->patch('/config/{id}/enable', 'Config@enable')->name('config-enable');
    $router->patch('/config/{id}/disable', 'Config@disable')->name('config-disable');
    $router->put('/config/setting', 'Config@setting')->name('config-setting');
    
    $router->get('/log', 'AdminLog@index')->name('log-index');
    $router->get('/log/{id}', 'AdminLog@detail')->name('log-detail');
    $router->delete('/log/clear', 'AdminLog@clear')->name('log-clear');
    $router->delete('/log/{id}', 'AdminLog@delete')->name('log-delete');
    
    $router->get('/extension/index', 'Extension@index')->name('extension-index');
    $router->get('/extension/local', 'Extension@local')->name('extension-local');
    $router->post('/extension/install/{name}', 'Extension@install')->name('extension-install');
    $router->delete('/extension/uninstall/{name}', 'Extension@uninstall')->name('extension-uninstall');
    $router->put('/extension/upgrade/{name}', 'Extension@upgrade')->name('extension-upgrade');
    $router->put('/extension/{name}/config', 'Extension@config')->name('extension-config');
    $router->patch('/extension/{name}/enable', 'Extension@enable')->name('extension-enable');
    $router->patch('/extension/{name}/disable', 'Extension@disable')->name('extension-disable');
    $router->post('/extension/upload', 'Extension@upload')->name('extension-upload');
    
    $router->get('/system/info', 'System@info')->name('system-info');
    $router->get('/system/lang', 'System@lang')->name('system-lang');
    $router->post('/system/cache', 'System@cache')->name('system-cache');
    $router->post('/system/clear-cache', 'System@clearCache')->name('system-clear-cache');
});
