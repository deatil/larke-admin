<?php

declare (strict_types = 1);

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * 事件
 *
 * @create 2024-6-18
 * @author deatil
 */
class Event extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke-admin.event';
    }
}
