<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Illuminate\Support\Facades\Log;

use Larke\Admin\Event;

/*
 * 权限 Token 错误
 *
 * @create 2024-6-19
 * @author deatil
 */
class PassportLoginAccessTokenError
{
    public function handle(Event\PassportLoginAccessTokenError $event)
    {
        $message = $event->message;
        
        Log::error('larke-admin-login accessToken error: ' . $message);
    }
}
