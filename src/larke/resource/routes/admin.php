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
        $router->get('/auth/rule/detail', 'AuthRule@detail')->name('auth-rule-detail');
        $router->post('/auth/rule/create', 'AuthRule@create')->name('auth-rule-create');
        $router->put('/auth/rule/update', 'AuthRule@update')->name('auth-rule-update');
        $router->patch('/auth/rule/listorder', 'AuthRule@listorder')->name('auth-rule-listorder');
        $router->patch('/auth/rule/enable', 'AuthRule@enable')->name('auth-rule-enable');
        $router->patch('/auth/rule/disable', 'AuthRule@disable')->name('auth-rule-disable');
        $router->delete('/auth/rule/delete', 'AuthRule@delete')->name('auth-rule-delete');
        
        $router->get('/auth/group/index', 'AuthGroup@index')->name('auth-group-index');
        $router->get('/auth/group/index-tree', 'AuthGroup@indexTree')->name('auth-group-index-tree');
        $router->get('/auth/group/index-children', 'AuthGroup@indexChildren')->name('auth-group-index-children');
        $router->get('/auth/group/detail', 'AuthGroup@detail')->name('auth-group-detail');
        $router->post('/auth/group/create', 'AuthGroup@create')->name('auth-group-create');
        $router->put('/auth/group/update', 'AuthGroup@update')->name('auth-group-update');
        $router->patch('/auth/group/listorder', 'AuthGroup@listorder')->name('auth-group-listorder');
        $router->patch('/auth/group/enable', 'AuthGroup@enable')->name('auth-group-enable');
        $router->patch('/auth/group/disable', 'AuthGroup@disable')->name('auth-group-disable');
        $router->delete('/auth/group/delete', 'AuthGroup@delete')->name('auth-group-delete');
        $router->put('/auth/group/access', 'AuthGroup@access')->name('auth-group-access');
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
    $router->get('/attachment/detail', 'Attachment@detail')->name('attachment-detail');
    $router->patch('/attachment/enable', 'Attachment@enable')->name('attachment-enable');
    $router->patch('/attachment/disable', 'Attachment@disable')->name('attachment-disable');
    $router->delete('/attachment/delete', 'Attachment@delete')->name('attachment-delete');
    $router->post('/attachment/upload', 'Attachment@upload')->name('attachment-upload');
    $router->get('/attachment/download/code', 'Attachment@downloadCode')->name('attachment-download-code');
    $router->get('/attachment/download', 'Attachment@download')->name('attachment-download');
    
    $router->get('/admin/index', 'Admin@index')->name('admin-index');
    $router->get('/admin/detail', 'Admin@detail')->name('admin-detail');
    $router->get('/admin/rules', 'Admin@rules')->name('admin-rules');
    $router->post('/admin/create', 'Admin@create')->name('admin-create');
    $router->put('/admin/update', 'Admin@update')->name('admin-update');
    $router->patch('/admin/enable', 'Admin@enable')->name('admin-enable');
    $router->patch('/admin/disable', 'Admin@disable')->name('admin-disable');
    $router->delete('/admin/delete', 'Admin@delete')->name('admin-delete');
    $router->put('/admin/password', 'Admin@changePasssword')->name('admin-password');
    $router->post('/admin/logout', 'Admin@logout')->name('admin-logout');
    $router->put('/admin/access', 'Admin@access')->name('admin-access');
    
    $router->get('/config/index', 'Config@index')->name('config-index');
    $router->get('/config/detail', 'Config@detail')->name('config-detail');
    $router->post('/config/create', 'Config@create')->name('config-create');
    $router->put('/config/update', 'Config@update')->name('config-update');
    $router->patch('/config/listorder', 'Config@listorder')->name('config-listorder');
    $router->patch('/config/enable', 'Config@enable')->name('config-enable');
    $router->patch('/config/disable', 'Config@disable')->name('config-disable');
    $router->delete('/config/delete', 'Config@delete')->name('config-delete');
    $router->put('/config/setting', 'Config@setting')->name('config-setting');
    
    $router->get('/log/index', 'Log@index')->name('log-index');
    $router->get('/log/detail', 'Log@detail')->name('log-detail');
    $router->delete('/log/delete', 'Log@delete')->name('log-delete');
    
    $router->get('/extension/index', 'Extension@index')->name('extension-index');
    $router->get('/extension/local', 'Extension@local')->name('extension-local');
    $router->put('/extension/config', 'Extension@config')->name('extension-config');
    $router->post('/extension/install', 'Extension@install')->name('extension-install');
    $router->delete('/extension/uninstall', 'Extension@uninstall')->name('extension-uninstall');
    $router->put('/extension/upgrade', 'Extension@upgrade')->name('extension-upgrade');
    $router->patch('/extension/enable', 'Extension@enable')->name('extension-enable');
    $router->patch('/extension/disable', 'Extension@disable')->name('extension-disable');
    $router->patch('/extension/listorder', 'Extension@listorder')->name('extension-listorder');
    $router->post('/extension/upload', 'Extension@upload')->name('extension-upload');
    
    $router->get('/system/info', 'System@info')->name('system-info');
    $router->post('/system/cache', 'System@cache')->name('system-cache');
    $router->post('/system/clear-cache', 'System@clearCache')->name('system-clear-cache');
});
