<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Illuminate\Support\Facades\Log;

/*
 * 权限 Token 错误
 *
 * @create 2024-6-19
 * @author deatil
 */
class PassportLoginAccessTokenError
{
    public function handle($message)
    {
        Log::error('larke-admin-login accessToken error: ' . $message);
    }
}
