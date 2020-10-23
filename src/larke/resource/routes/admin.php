<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('larke.route.prefix'),
    'middleware' => config('larke.route.middleware'),
], function ($router) {
    $router->namespace(config('larke.route.namespace'))->group(function ($router) {
        $router->post('/passport/login', 'Passport@login')->name('larke-admin-passport-login');
        $router->post('/passport/logout', 'Passport@logout')->name('larke-admin-passport-logout');
        $router->post('/passport/refresh-token', 'Passport@refreshToken')->name('larke-admin-passport-refresh-token');
        
        $router->get('/profile', 'Profile@index')->name('larke-admin-profile');
        $router->post('/profile/update', 'Profile@update')->name('larke-admin-profile-update');
        $router->post('/profile/password', 'Profile@changePasssword')->name('larke-admin-profile-password');
        
        $router->post('/attachment/upload', 'Attachment@upload')->name('larke-admin-attachment-upload');
        $router->get('/attachment/index', 'Attachment@index')->name('larke-admin-attachment-index');
        $router->get('/attachment/detail', 'Attachment@detail')->name('larke-admin-attachment-detail');
        $router->post('/attachment/delete', 'Attachment@delete')->name('larke-admin-attachment-delete');

        $router->get('/admin/index', 'Admin@index')->name('larke-admin-admin-index');
        $router->get('/admin/detail', 'Admin@detail')->name('larke-admin-admin-detail');
        $router->post('/admin/delete', 'Admin@delete')->name('larke-admin-admin-delete');
        $router->post('/admin/create', 'Admin@create')->name('larke-admin-admin-create');
        $router->post('/admin/update', 'Admin@update')->name('larke-admin-admin-update');
        $router->post('/admin/password', 'Admin@changePasssword')->name('larke-admin-admin-password');

        $router->get('/log/index', 'Log@index')->name('larke-admin-log-index');
        $router->get('/log/detail', 'Log@detail')->name('larke-admin-log-detail');
        $router->post('/log/delete', 'Log@delete')->name('larke-admin-log-delete');

    });
});
