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
    public function handle(Event\PassportLoginAfter $event)
    {
        // jwt 数据
        $jwt = $event->jwt;
        
        // token
        $accessToken = $jwt['access_token'];
        
        // 权限 token 签发时间
        $decodeAccessToken = app('larke-admin.auth-token')
                ->decodeAccessToken($accessToken);
        $decodeAccessTokenIat = $decodeAccessToken->getClaim('iat');
        $refreshAdminid = $decodeRefreshToken->getData('adminid');
        
        // 更新信息
        AdminModel::where('id', $refreshAdminid)->update([
            'refresh_time' => $decodeAccessTokenIat, 
            'refresh_ip' => request()->ip(),
        ]);
    }
}
