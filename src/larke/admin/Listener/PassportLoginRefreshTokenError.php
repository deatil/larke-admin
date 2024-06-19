<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Illuminate\Support\Facades\Log;

use Larke\Admin\Event;

/*
 * 刷新 Token 错误
 *
 * @create 2024-6-19
 * @author deatil
 */
class PassportLoginRefreshTokenError
{
    public function handle(Event\PassportLoginRefreshTokenError $event)
    {
        $message = $event->message;
        
        Log::error('larke-admin-login refreshToken error: ' . $message);
    }
}
