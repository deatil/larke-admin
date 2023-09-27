<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Larke\Admin\Event;
use Larke\Admin\Model\Admin as AdminModel;

/*
 * 更新信息
 *
 * @create 2021-8-19
 * @author deatil
 */
class PassportRefreshTokenAfter
{
    public function handle(Event\PassportRefreshTokenAfter $event)
    {
        // jwt 数据
        $jwt = $event->jwt;
        
        // 权限 token 签发时间
        $decodeAccessToken = app('larke-admin.auth-token')
            ->decodeAccessToken($jwt['access_token']);
        
        $iat = $decodeAccessToken->getClaim('iat')->getTimestamp();
        $adminid = $decodeAccessToken->getData('adminid');
        
        // 更新信息
        AdminModel::where('id', $adminid)->update([
            'refresh_time' => $iat, 
            'refresh_ip'   => request()->ip(),
        ]);
    }
}
