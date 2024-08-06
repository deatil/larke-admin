<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Illuminate\Support\Facades\Log;

/*
 * 刷新 Token 错误
 *
 * @create 2024-6-19
 * @author deatil
 */
class PassportLoginRefreshTokenError
{
    public function handle($message)
    {
        Log::error('larke-admin-login refreshToken error: ' . $message);
    }
}
