<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Illuminate\Support\Facades\Log;

use Larke\Admin\Event;

/*
 * 登陆密钥错误
 *
 * @create 2024-6-19
 * @author deatil
 */
class PassportLoginKeyError
{
    public function handle(Event\PassportLoginKeyError $event)
    {
        $message = $event->message;
        
        Log::error('larke-admin-login loadkey error: ' . $message);
    }
}
