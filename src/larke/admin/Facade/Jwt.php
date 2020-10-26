<?php

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Jwt
 *
 * @create 2020-10-26
 * @author deatil
 */
class Jwt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke.jwt';
    }
}
