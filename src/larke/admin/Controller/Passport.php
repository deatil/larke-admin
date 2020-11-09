<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Captcha\Captcha;
use Larke\Admin\Service\Password as PasswordService;
use Larke\Admin\Model\Admin as AdminModel;

use Larke\Admin\Event\PassportLoginBefore as PassportLoginBeforeEvent;
use Larke\Admin\Event\PassportLoginAfter as PassportLoginAfterEvent;

/**
 * 登陆
 *
 * @title 登陆
 * @desc 系统登陆管理
 * @order 100
 * @auth true
 *
 * @create 2020-10-19
 * @author deatil
 */
class Passport extends Base
{
    /**
     * 验证码
     */
    public function captcha(Request $request)
    {
        $data = $request->all();
        
        $Captcha = new Captcha();
        
        $captcha = $Captcha->getData();
        $captchaUniq = $Captcha->getUniqid();
        
        $captchaKey = config('larke.passport.header_captcha_key');
        return $this->successJson(__('获取成功'), [
            'captcha' => $captcha,
        ], 0, [
            $captchaKey => $captchaUniq,
        ]);
    }
    
    /**
     * 登陆
     */
    public function login(Request $request)
    {
        // 监听事件
        event(new PassportLoginBeforeEvent($request));
        
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required',
            'password' => 'required|size:32',
            'captcha' => 'required|size:4',
        ], [
            'name.required' => __('账号不能为空'),
            'password.required' => __('密码不能为空'),
            'password.size' => __('密码错误'),
            'captcha.required' => __('验证码不能为空'),
            'captcha.size' => __('验证码位数错误'),
        ]);
        
        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
        }
        
        $name = $request->get('name');
        
        $captchaKey = config('larke.passport.header_captcha_key');
        $captchaUniq = $request->header($captchaKey);
        $captcha = $request->get('captcha');
        if (!Captcha::check($captcha, $captchaUniq)) {
            return $this->errorJson(__('验证码错误'));
        }
        
        // 校验密码
        $admin = AdminModel::where('name', $name)
            ->first();
        if (empty($admin)) {
            return $this->errorJson(__('帐号错误'));
        }
        
        $adminInfo = $admin->toArray();
        $password = $request->post('password');
        
        $encryptPassword = (new PasswordService())
            ->withSalt(config('larke.passport.password_salt'))
            ->encrypt($password, $adminInfo['password_salt']); 
        if ($encryptPassword != $adminInfo['password']) {
            return $this->errorJson(__('账号密码错误'));
        }
        
        if ($adminInfo['status'] == 0) {
            return $this->errorJson(__('用户已被禁用或者不存在'));
        }
        
        // 生成 accessToken
        $expiredIn = config('larke.passport.access_expired_in', 86400);
        $accessToken = app('larke.jwt')->withClaim([
            'adminid' => $adminInfo['id'],
        ])->withExp($expiredIn)
            ->withJti(config('larke.passport.access_token_id'))
            ->encode()
            ->getToken();
        if (empty($accessToken)) {
            return $this->errorJson(__('登陆失败'));
        }
        
        // 刷新token
        $refreshExpiredIn = config('larke.passport.refresh_expired_in', 300);
        $refreshToken = app('larke.jwt')->withClaim([
            'adminid' => $adminInfo['id'],
        ])->withExp($refreshExpiredIn)
            ->withJti(config('larke.passport.refresh_token_id'))
            ->encode()
            ->getToken();
        if (empty($refreshToken)) {
            return $this->errorJson(__('登陆失败'));
        }
        
        // 更新信息
        $admin->update([
            'last_active' => time(), 
            'last_ip' => $request->ip(),
        ]);
        
        // 监听事件
        event(new PassportLoginAfterEvent($admin));
        
        return $this->successJson(__('登录成功'), [
            'access_token' => $accessToken,
            'expired_in' => $expiredIn, // 过期时间
            'refresh_token' => $refreshToken,
        ]);
    }
    
    /**
     * 刷新token
     */
    public function refreshToken(Request $request)
    {
        $refreshToken = $request->get('refresh_token');
        if (empty($refreshToken)) {
            return $this->errorJson(__('refreshToken不能为空'));
        }
        
        if (app('larke.cache')->has(md5($refreshToken))) {
            return $this->errorJson(__('refreshToken已失效'));
        }
        
        $refreshJwt = app('larke.jwt')
            ->withJti(config('larke.passport.refresh_token_id'))
            ->withToken($refreshToken)
            ->decode();
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            return $this->errorJson(__('token已过期'));
        }
        
        $refreshAdminid = $refreshJwt->getClaim('adminid');
        if ($refreshAdminid === false) {
            $this->errorJson(__('token错误'));
        }
        
        $expiredIn = config('larke.passport.access_expired_in', 86400);
        $newAccessToken = app('larke.jwt')->withClaim([
            'adminid' => $refreshAdminid,
        ])->withExp($expiredIn)
            ->withJti(config('larke.passport.access_token_id'))
            ->encode()
            ->getToken();
        if (empty($newAccessToken)) {
            return $this->errorJson(__('刷新Token失败'));
        }
        
        return $this->successJson(__('刷新Token成功'), [
            'access_token' => $newAccessToken,
            'expired_in' => $expiredIn,
        ]);
    }
    
    /**
     * 退出
     */
    public function logout(Request $request)
    {
        $refreshToken = $request->get('refresh_token');
        if (empty($refreshToken)) {
            return $this->errorJson(__('refreshToken不能为空'));
        }
        
        if (app('larke.cache')->has(md5($refreshToken))) {
            return $this->errorJson(__('refreshToken已失效'));
        }
        
        // 刷新Token
        $refreshJwt = app('larke.jwt')
            ->withJti(config('larke.passport.refresh_token_id'))
            ->withToken($refreshToken)
            ->decode();
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            return $this->errorJson(__('refreshToken已过期'));
        }
        
        $refreshAdminid = $refreshJwt->getClaim('adminid');
        if ($refreshAdminid === false) {
            $this->errorJson(__('token错误'));
        }
        
        $accessAdminid = app('larke.admin')->getId();
        if ($accessAdminid != $refreshAdminid) {
            return $this->errorJson(__('退出失败'));
        }
        
        $accessToken = app('larke.admin')->getAccessToken();
        
        $refreshTokenExpiredIn = $refreshJwt->getClaim('exp') - $refreshJwt->getClaim('iat');
        
        // 添加缓存黑名单
        app('larke.cache')->add(md5($accessToken), 'out', $refreshTokenExpiredIn);
        app('larke.cache')->add(md5($refreshToken), 'out', $refreshTokenExpiredIn);
        
        return $this->successJson(__('退出成功'));
    }
    
}
