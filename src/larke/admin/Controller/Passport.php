<?php

namespace Larke\Admin\Controller;

use Larke\Admin\Service\JwtAuth;
use Larke\Admin\Service\Password as PasswordService;
use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\AdminLog as AdminLogModel;

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
        $name = request()->get('name');
        if (empty($name)) {
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
        $adminInfo = AdminModel::where('name', $name)->first()->toArray();
        if (empty($adminInfo)) {
            $this->errorJson('帐号错误');
        }
        
        $password2 = (new PasswordService())
            ->withSalt(config('larke.passport.salt'))
            ->encrypt($password, $adminInfo['passport_salt']); 
        if ($password2 != $adminInfo['password']) {
            $this->errorJson('账号密码错误');
        }
        
        if ($adminInfo['status'] == 0) {
            $this->errorJson('用户已被禁用或者不存在');
        }
        
        // 获取jwt的句柄
        $jwtAuth = JwtAuth::getInstance();
        $token = $jwtAuth->withClaim([
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
        AdminLogModel::record([
            'admin_id' => $adminInfo['id'],
            'admin_name' => $adminInfo['name'],
            'info' => '登陆成功',
            'status' => 1,
        ]);
        
        $this->successJson('登录成功', [
            'token' => $token,
        ]);
    }
}
