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

    });
});
