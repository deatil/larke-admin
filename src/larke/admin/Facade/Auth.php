<?php

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Auth
 *
 * @create 2020-10-26
 * @author deatil
 */
class Auth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke.auth';
    }
}
