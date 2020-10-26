<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('larke.route.prefix'),
    'middleware' => config('larke.route.middleware'),
], function ($router) {
    $router->namespace(config('larke.route.namespace'))->group(function ($router) {
        $router->post('/sys/clear-cache', 'Sys@clearCache')->name('larke-admin-sys-clear-cache');
        
        $router->get('/passport/captcha', 'Passport@captcha')->name('larke-admin-passport-captcha');
        $router->post('/passport/login', 'Passport@login')->name('larke-admin-passport-login');
        $router->post('/passport/logout', 'Passport@logout')->name('larke-admin-passport-logout');
        $router->post('/passport/refresh-token', 'Passport@refreshToken')->name('larke-admin-passport-refresh-token');
        
        $router->get('/profile', 'Profile@index')->name('larke-admin-profile');
        $router->post('/profile/update', 'Profile@update')->name('larke-admin-profile-update');
        $router->post('/profile/password', 'Profile@changePasssword')->name('larke-admin-profile-password');
        $router->get('/profile/menus', 'Profile@menus')->name('larke-admin-profile-menus');
        
        $router->get('/attachment/index', 'Attachment@index')->name('larke-admin-attachment-index');
        $router->get('/attachment/detail', 'Attachment@detail')->name('larke-admin-attachment-detail');
        $router->post('/attachment/delete', 'Attachment@delete')->name('larke-admin-attachment-delete');
        $router->post('/attachment/upload', 'Attachment@upload')->name('larke-admin-attachment-upload');
        $router->get('/attachment/download', 'Attachment@download')->name('larke-admin-attachment-download');
        
        $router->get('/admin/index', 'Admin@index')->name('larke-admin-admin-index');
        $router->get('/admin/detail', 'Admin@detail')->name('larke-admin-admin-detail');
        $router->post('/admin/create', 'Admin@create')->name('larke-admin-admin-create');
        $router->post('/admin/update', 'Admin@update')->name('larke-admin-admin-update');
        $router->post('/admin/delete', 'Admin@delete')->name('larke-admin-admin-delete');
        $router->post('/admin/password', 'Admin@changePasssword')->name('larke-admin-admin-password');
        $router->post('/admin/logout', 'Admin@logout')->name('larke-admin-admin-logout');
        
        $router->get('/auth/rule/index', 'AuthRule@index')->name('larke-admin-auth-rule-index');
        $router->get('/auth/rule/group-index', 'AuthRule@groupForIndex')->name('larke-admin-auth-rule-group-index');
        $router->get('/auth/rule/detail', 'AuthRule@detail')->name('larke-admin-auth-rule-detail');
        $router->post('/auth/rule/create', 'AuthRule@create')->name('larke-admin-auth-rule-create');
        $router->post('/auth/rule/update', 'AuthRule@update')->name('larke-admin-auth-rule-update');
        $router->post('/auth/rule/delete', 'AuthRule@delete')->name('larke-admin-auth-rule-delete');
        
        $router->get('/auth/group/index', 'AuthGroup@index')->name('larke-admin-auth-group-index');
        $router->get('/auth/group/group-index', 'AuthGroup@groupForIndex')->name('larke-admin-auth-group-group-index');
        $router->get('/auth/group/detail', 'AuthGroup@detail')->name('larke-admin-auth-group-detail');
        $router->post('/auth/group/create', 'AuthGroup@create')->name('larke-admin-auth-group-create');
        $router->post('/auth/group/update', 'AuthGroup@update')->name('larke-admin-auth-group-update');
        $router->post('/auth/group/delete', 'AuthGroup@delete')->name('larke-admin-auth-group-delete');
        
        $router->get('/config/index', 'Config@index')->name('larke-admin-config-index');
        $router->get('/config/detail', 'Config@detail')->name('larke-admin-config-detail');
        $router->post('/config/create', 'Config@create')->name('larke-admin-config-create');
        $router->post('/config/update', 'Config@update')->name('larke-admin-config-update');
        $router->post('/config/delete', 'Config@delete')->name('larke-admin-config-delete');
        $router->post('/config/setting', 'Config@setting')->name('larke-admin-config-setting');
        
        $router->get('/log/index', 'Log@index')->name('larke-admin-log-index');
        $router->get('/log/detail', 'Log@detail')->name('larke-admin-log-detail');
        $router->post('/log/delete', 'Log@delete')->name('larke-admin-log-delete');
        
    });
});
