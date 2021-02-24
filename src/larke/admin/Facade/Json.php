<?php

declare (strict_types = 1);

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Json
 *
 * @create 2020-10-26
 * @author deatil
 */
class Json extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke-admin.json';
    }
}
