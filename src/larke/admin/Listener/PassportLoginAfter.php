<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Larke\Admin\Event;

/*
 * 更新登陆信息
 *
 * @create 2020-11-10
 * @author deatil
 */
class PassportLoginAfter
{
    public function handle(Event\PassportLoginAfter $event)
    {
        $event->admin->update([
            'last_active' => time(), 
            'last_ip' => request()->ip(),
        ]);
    }
}
