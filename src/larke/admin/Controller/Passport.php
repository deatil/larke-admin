<?php

namespace Larke\Admin\Controller;

use Larke\Admin\Service\JwtAuth;
use Larke\Admin\Service\Password as PasswordFacade;
use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\Log as LogModel;

/**
 * 登陆
 *
 * @create 2020-10-19
 * @author deatil
 */
class Passport extends Base
{
    /**
     * 登陆
     */
    public function login()
    {
        $username = request()->get('username');
        if (empty($username)) {
            $this->errorJson('账号不能为空');
        }

        $password = request()->post('password');
        if (empty($password)) {
            $this->errorJson('密码不能为空');
        }
        if (strlen($password) != 32) {
            $this->errorJson('用户密码错误');
        }
        
        // 校验密码
        $adminInfo = AdminModel::where('adminname', $adminname)
            ->find();
        if (empty($adminInfo)) {
            $this->errorJson('帐号错误');
        }
        
        $password2 = PasswordFacade::encryptPassword($password, $adminInfo['salt']); 
        if ($password2 != $user['password']) {
            $this->errorJson('账号密码错误');
        }
        
        if ($adminInfo['status'] == 0) {
            $this->errorJson('用户已被禁用或者不存在');
        }
        
        // 获取jwt的句柄
        $jwtAuth = JwtAuth::getInstance();
        $token = $jwtAuth->setClaim([
            'adminid' => $adminInfo['id'],
        ])->encode()->getToken();
        if (empty($token)) {
            $this->errorJson('登陆失败');
        }
        
        // 更新信息
        AdminModel::where('id', $adminInfo['id'])->update([
            'last_active' => time(), 
            'last_ip' => request()->ip(),
        ]);
        
        // 记录日志
        LogModel::record([
            'user_id' => $user['id'],
        ]);
        
        $this->successJson('登录成功', [
            'token' => $token,
        ]);
    }
}
