<?php

declare (strict_types = 1);

namespace Larke\Admin\Provider;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

// use directory
use Larke\Admin\Listener;

use function Larke\Admin\add_action;

class EventServiceProvider extends BaseServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // 登陆密钥错误
        add_action('admin.passport.login_key_error', Listener\PassportLoginKeyError::class);

        // 登陆权限 Token 错误
        add_action('admin.passport.login_access_token_error', Listener\PassportLoginAccessTokenError::class);

        // 登陆刷新 Token 错误
        add_action('admin.passport.login_refresh_token_error', Listener\PassportLoginRefreshTokenError::class);

        // 登陆
        add_action('admin.passport.login_after', Listener\PassportLoginAfter::class);

        // 刷新 token
        add_action('admin.passport.refresh_token_after', Listener\PassportRefreshTokenAfter::class);

        // 退出
        add_action('admin.passport.logout_after', Listener\PassportLogoutAfter::class);
    }

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
