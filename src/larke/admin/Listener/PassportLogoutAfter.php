<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Larke\Admin\Event;
use Larke\Admin\Model\Admin as AdminModel;

/*
 * 更新信息
 *
 * @create 2021-8-20
 * @author deatil
 */
class PassportLogoutAfter
{
    public function handle(Event\PassportLogoutAfter $event)
    {
        $adminid = app('larke-admin.auth-admin')->getId();
        
        // 更新信息
        AdminModel::where('id', $adminid)->update([
            'refresh_time' => time(), 
            'refresh_ip' => request()->ip(),
        ]);
    }
}
