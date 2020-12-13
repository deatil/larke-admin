<?php

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Cache
 *
 * @create 2020-10-27
 * @author deatil
 */
class Cache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke.admin.cache';
    }
}
