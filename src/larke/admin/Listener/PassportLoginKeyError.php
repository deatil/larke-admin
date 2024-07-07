<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Illuminate\Support\Facades\Log;

/*
 * 登陆密钥错误
 *
 * @create 2024-6-19
 * @author deatil
 */
class PassportLoginKeyError
{
    public function handle($message)
    {
        Log::error('larke-admin-login loadkey error: ' . $message);
    }
}
