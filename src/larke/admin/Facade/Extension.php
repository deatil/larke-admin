<?php

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Extension
 *
 * @create 2020-10-30
 * @author deatil
 */
class Extension extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke.extension';
    }
}
