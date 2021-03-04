<?php

declare (strict_types = 1);

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * 权限token
 *
 * @create 2021-3-3
 * @author deatil
 */
class AuthToken extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke-admin.auth-token';
    }
}
