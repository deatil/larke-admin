<?php

declare (strict_types = 1);

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * 权限
 *
 * @create 2022-5-7
 * @author deatil
 */
class Permission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke-admin.permission';
    }
}
