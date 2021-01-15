<?php

declare (strict_types = 1);

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * 扩展
 *
 * @create 2020-10-30
 * @author deatil
 */
class Extension extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke.admin.extension';
    }
}
