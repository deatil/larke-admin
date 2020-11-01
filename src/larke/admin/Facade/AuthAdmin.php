<?php

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * AuthAdmin
 *
 * @create 2020-10-26
 * @author deatil
 */
class AuthAdmin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke.admin';
    }
}
