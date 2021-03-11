<?php

declare (strict_types = 1);

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * 当前管理员信息
 *
 * @create 2020-10-26
 * @author deatil
 */
class AuthAdmin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke-admin.auth-admin';
    }
}
