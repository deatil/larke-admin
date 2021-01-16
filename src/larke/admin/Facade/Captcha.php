<?php

declare (strict_types = 1);

namespace Larke\Admin\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * 验证码
 *
 * @create 2021-1-16
 * @author deatil
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke.admin.captcha';
    }
}
