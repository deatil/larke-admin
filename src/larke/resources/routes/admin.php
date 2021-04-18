<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'domain' => config('larkeadmin.route.domain'),
    'prefix' => config('larkeadmin.route.prefix'),
    'middleware' => config('larkeadmin.route.middleware'),
    'namespace' => config('larkeadmin.route.namespace'),
    'as' => config('larkeadmin.route.as'),
], function ($router) {
    $router->group([
        'middleware' => config('larkeadmin.route.admin_middleware'),
    ], function ($router) {
        // 权限菜单
        $router->get('/auth/rule', 'AuthRule@index')->name('auth-rule.index');
        $router->get('/auth/rule/tree', 'AuthRule@indexTree')->name('auth-rule.index-tree');
        $router->get('/auth/rule/children', 'AuthRule@indexChildren')->name('auth-rule.index-children');
        $router->get('/auth/rule/{id}', 'AuthRule@detail')->name('auth-rule.detail')->where('id', '[A-Za-z0-9]+');
        $router->post('/auth/rule', 'AuthRule@create')->name('auth-rule.create');
        $router->put('/auth/rule/{id}', 'AuthRule@update')->name('auth-rule.update')->where('id', '[A-Za-z0-9]+');
        $router->delete('/auth/rule/clear', 'AuthRule@clear')->name('auth-rule.clear');
        $router->delete('/auth/rule/{id}', 'AuthRule@delete')->name('auth-rule.delete')->where('id', '[A-Za-z0-9]+');
        $router->patch('/auth/rule/{id}/sort', 'AuthRule@listorder')->name('auth-rule.listorder')->where('id', '[A-Za-z0-9]+');
        $router->patch('/auth/rule/{id}/enable', 'AuthRule@enable')->name('auth-rule.enable')->where('id', '[A-Za-z0-9]+');
        $router->patch('/auth/rule/{id}/disable', 'AuthRule@disable')->name('auth-rule.disable')->where('id', '[A-Za-z0-9]+');
        
        // 管理分组
        $router->get('/auth/group', 'AuthGroup@index')->name('auth-group.index');
        $router->get('/auth/group/tree', 'AuthGroup@indexTree')->name('auth-group.index-tree');
        $router->get('/auth/group/children', 'AuthGroup@indexChildren')->name('auth-group.index-children');
        $router->get('/auth/group/{id}', 'AuthGroup@detail')->name('auth-group.detail')->where('id', '[A-Za-z0-9]+');
        $router->post('/auth/group', 'AuthGroup@create')->name('auth-group.create');
        $router->put('/auth/group/{id}', 'AuthGroup@update')->name('auth-group.update')->where('id', '[A-Za-z0-9]+');
        $router->delete('/auth/group/{id}', 'AuthGroup@delete')->name('auth-group.delete')->where('id', '[A-Za-z0-9]+');
        $router->patch('/auth/group/{id}/sort', 'AuthGroup@listorder')->name('auth-group.listorder')->where('id', '[A-Za-z0-9]+');
        $router->patch('/auth/group/{id}/enable', 'AuthGroup@enable')->name('auth-group.enable')->where('id', '[A-Za-z0-9]+');
        $router->patch('/auth/group/{id}/disable', 'AuthGroup@disable')->name('auth-group.disable')->where('id', '[A-Za-z0-9]+');
        $router->patch('/auth/group/{id}/access', 'AuthGroup@access')->name('auth-group.access')->where('id', '[A-Za-z0-9]+');
    });
    
    // 登陆
    $router->get('/passport/captcha', 'Passport@captcha')->name('passport.captcha');
    $router->post('/passport/login', 'Passport@login')->name('passport.login');
    $router->delete('/passport/logout', 'Passport@logout')->name('passport.logout');
    $router->put('/passport/refresh-token', 'Passport@refreshToken')->name('passport.refresh-token');
    
    // 个人信息
    $router->get('/profile', 'Profile@index')->name('profile');
    $router->put('/profile/update', 'Profile@update')->name('profile.update');
    $router->patch('/profile/avatar', 'Profile@updateAvatar')->name('profile.avatar');
    $router->patch('/profile/password', 'Profile@updatePasssword')->name('profile.password');
    $router->get('/profile/rules', 'Profile@rules')->name('profile.rules');
    
    // 附件
    $router->get('/attachment', 'Attachment@index')->name('attachment.index');
    $router->get('/attachment/{id}', 'Attachment@detail')->name('attachment.detail')->where('id', '[A-Za-z0-9]+');
    $router->patch('/attachment/{id}/enable', 'Attachment@enable')->name('attachment.enable')->where('id', '[A-Za-z0-9]+');
    $router->patch('/attachment/{id}/disable', 'Attachment@disable')->name('attachment.disable')->where('id', '[A-Za-z0-9]+');
    $router->delete('/attachment/{id}', 'Attachment@delete')->name('attachment.delete')->where('id', '[A-Za-z0-9]+');
    $router->get('/attachment/downcode/{id}', 'Attachment@downloadCode')->name('attachment.download-code')->where('id', '[A-Za-z0-9]+');
    $router->get('/attachment/download/{code}', 'Attachment@download')->name('attachment.download')->where('code', '[A-Za-z0-9]+');
    
    // 上传
    $router->post('/upload/file', 'Upload@file')->name('upload.file');
    
    // 管理员
    $router->get('/admin', 'Admin@index')->name('admin.index');
    $router->get('/admin/{id}', 'Admin@detail')->name('admin.detail')->where('id', '[A-Za-z0-9]+');
    $router->get('/admin/{id}/rules', 'Admin@rules')->name('admin.rules')->where('id', '[A-Za-z0-9]+');
    $router->post('/admin', 'Admin@create')->name('admin.create');
    $router->put('/admin/{id}', 'Admin@update')->name('admin.update')->where('id', '[A-Za-z0-9]+');
    $router->delete('/admin/{id}', 'Admin@delete')->name('admin.delete')->where('id', '[A-Za-z0-9]+');
    $router->patch('/admin/{id}/enable', 'Admin@enable')->name('admin.enable')->where('id', '[A-Za-z0-9]+');
    $router->patch('/admin/{id}/disable', 'Admin@disable')->name('admin.disable')->where('id', '[A-Za-z0-9]+');
    $router->patch('/admin/{id}/avatar', 'Admin@updateAvatar')->name('admin.avatar')->where('id', '[A-Za-z0-9]+');
    $router->patch('/admin/{id}/password', 'Admin@updatePasssword')->name('admin.password')->where('id', '[A-Za-z0-9]+');
    $router->patch('/admin/{id}/access', 'Admin@access')->name('admin.access')->where('id', '[A-Za-z0-9]+');
    $router->delete('/admin/logout/{refreshToken}', 'Admin@logout')->name('admin.logout');
    
    // 配置
    $router->get('/config/settings', 'Config@settings')->name('config.settings');
    $router->get('/config/list', 'Config@lists')->name('config.lists');
    $router->get('/config', 'Config@index')->name('config.index');
    $router->get('/config/{id}', 'Config@detail')->name('config.detail')->where('id', '[A-Za-z0-9]+');
    $router->post('/config', 'Config@create')->name('config.create');
    $router->put('/config/setting', 'Config@setting')->name('config.setting');
    $router->put('/config/{id}', 'Config@update')->name('config.update')->where('id', '[A-Za-z0-9]+');
    $router->delete('/config/{id}', 'Config@delete')->name('config.delete')->where('id', '[A-Za-z0-9]+');
    $router->patch('/config/{id}/sort', 'Config@listorder')->name('config.listorder')->where('id', '[A-Za-z0-9]+');
    $router->patch('/config/{id}/enable', 'Config@enable')->name('config.enable')->where('id', '[A-Za-z0-9]+');
    $router->patch('/config/{id}/disable', 'Config@disable')->name('config.disable')->where('id', '[A-Za-z0-9]+');
    
    // 日志
    $router->get('/log', 'AdminLog@index')->name('log.index');
    $router->get('/log/{id}', 'AdminLog@detail')->name('log.detail')->where('id', '[A-Za-z0-9]+');
    $router->delete('/log/clear', 'AdminLog@clear')->name('log.clear');
    $router->delete('/log/{id}', 'AdminLog@delete')->name('log.delete')->where('id', '[A-Za-z0-9]+');
    
    // 扩展
    $router->get('/extension/index', 'Extension@index')->name('extension.index');
    $router->get('/extension/local', 'Extension@local')->name('extension.local');
    $router->put('/extension/refresh', 'Extension@refreshLocal')->name('extension.refresh');
    $router->get('/extension/command/{name}', 'Extension@command')->name('extension.command')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->post('/extension/install/{name}', 'Extension@install')->name('extension.install')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->delete('/extension/uninstall/{name}', 'Extension@uninstall')->name('extension.uninstall')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->put('/extension/upgrade/{name}', 'Extension@upgrade')->name('extension.upgrade')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->put('/extension/{name}/repository-register', 'Extension@repositoryRegister')->name('extension.repository-register')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->put('/extension/{name}/repository-remove', 'Extension@repositoryRemove')->name('extension.repository-remove')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->put('/extension/{name}/config', 'Extension@config')->name('extension.config')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->patch('/extension/{name}/enable', 'Extension@enable')->name('extension.enable')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->patch('/extension/{name}/disable', 'Extension@disable')->name('extension.disable')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->patch('/extension/{name}/sort', 'Extension@listorder')->name('extension.listorder')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->post('/extension/upload', 'Extension@upload')->name('extension.upload');
    
    // 系统
    $router->get('/system/info', 'System@info')->name('system.info');
    $router->get('/system/lang', 'System@lang')->name('system.lang');
    $router->patch('/system/lang/{locale}', 'System@setLang')->name('system.set-lang')->where('locale', '[A-Za-z0-9\-\_]+');
    $router->post('/system/cache', 'System@cache')->name('system.cache');
    $router->post('/system/clear-cache', 'System@clearCache')->name('system.clear-cache');
});
