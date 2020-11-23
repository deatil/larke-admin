<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Captcha\Captcha;
use Larke\Admin\Service\Password as PasswordService;
use Larke\Admin\Model\Admin as AdminModel;

// for dir
use Larke\Admin\Event;

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
     *
     * @param  Request  $request
     * @return Response
     */
    public function captcha(Request $request)
    {
        $data = $request->all();
        
        $Captcha = new Captcha();
        
        $captcha = $Captcha->getData();
        $captchaUniq = $Captcha->getUniqid();
        
        $captchaKey = config('larkeadmin.passport.header_captcha_key');
        return $this->successJson(__('获取成功'), [
            'captcha' => $captcha,
        ], 0, [
            $captchaKey => $captchaUniq,
        ]);
    }
    
    /**
     * 登陆
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        // 监听事件
        event(new Event\PassportLoginBefore($request));
        
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
            return $this->errorJson($validator->errors()->first(), \ResponseCode::LOGIN_ERROR);
        }
        
        $name = $request->get('name');
        
        $captchaKey = config('larkeadmin.passport.header_captcha_key');
        $captchaUniq = $request->header($captchaKey);
        $captcha = $request->get('captcha');
        if (!Captcha::check($captcha, $captchaUniq)) {
            return $this->errorJson(__('验证码错误'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 校验密码
        $admin = AdminModel::where('name', $name)
            ->first();
        if (empty($admin)) {
            return $this->errorJson(__('帐号错误'), \ResponseCode::LOGIN_ERROR);
        }
        
        $adminInfo = $admin->toArray();
        $password = $request->post('password');
        
        $encryptPassword = (new PasswordService())
            ->withSalt(config('larkeadmin.passport.password_salt'))
            ->encrypt($password, $adminInfo['password_salt']); 
        if ($encryptPassword != $adminInfo['password']) {
            return $this->errorJson(__('账号密码错误'), \ResponseCode::LOGIN_ERROR);
        }
        
        if ($adminInfo['status'] == 0) {
            return $this->errorJson(__('用户已被禁用或者不存在'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 生成 accessToken
        $expiresIn = config('larkeadmin.passport.access_expires_in', 86400);
        $accessToken = app('larke.admin.jwt')
            ->withClaim([
                'adminid' => $adminInfo['id'],
            ])
            ->withExp($expiresIn)
            ->withJti(config('larkeadmin.passport.access_token_id'))
            ->encode()
            ->getToken();
        if (empty($accessToken)) {
            return $this->errorJson(__('登陆失败'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 刷新token
        $refreshExpiresIn = config('larkeadmin.passport.refresh_expires_in', 300);
        $refreshToken = app('larke.admin.jwt')
            ->withClaim([
                'adminid' => $adminInfo['id'],
            ])
            ->withExp($refreshExpiresIn)
            ->withJti(config('larkeadmin.passport.refresh_token_id'))
            ->encode()
            ->getToken();
        if (empty($refreshToken)) {
            return $this->errorJson(__('登陆失败'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 监听事件
        event(new Event\PassportLoginAfter($admin));
        
        return $this->successJson(__('登录成功'), [
            'access_token' => $accessToken,
            'expires_in' => $expiresIn, // 过期时间
            'refresh_token' => $refreshToken,
        ]);
    }
    
    /**
     * 刷新token
     *
     * @param  Request  $request
     * @return Response
     */
    public function refreshToken(Request $request)
    {
        $refreshToken = $request->get('refresh_token');
        if (empty($refreshToken)) {
            return $this->errorJson(__('refreshToken不能为空'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        if (app('larke.admin.cache')->has(md5($refreshToken))) {
            return $this->errorJson(__('refreshToken已失效'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        $refreshJwt = app('larke.admin.jwt')
            ->withJti(config('larkeadmin.passport.refresh_token_id'))
            ->withToken($refreshToken)
            ->decode();
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            return $this->errorJson(__('refreshToken已过期'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        $refreshAdminid = $refreshJwt->getClaim('adminid');
        if ($refreshAdminid === false) {
            return $this->errorJson(__('refreshToken错误'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        $expiresIn = config('larkeadmin.passport.access_expires_in', 86400);
        $newAccessToken = app('larke.admin.jwt')
            ->withClaim([
                'adminid' => $refreshAdminid,
            ])
            ->withExp($expiresIn)
            ->withJti(config('larkeadmin.passport.access_token_id'))
            ->encode()
            ->getToken();
        if (empty($newAccessToken)) {
            return $this->errorJson(__('刷新Token失败'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        // 监听事件
        event(new Event\PassportRefreshTokenAfter());
        
        return $this->successJson(__('刷新Token成功'), [
            'access_token' => $newAccessToken,
            'expires_in' => $expiresIn,
        ]);
    }
    
    /**
     * 退出
     *
     * @param  Request  $request
     * @return Response
     */
    public function logout(Request $request)
    {
        $refreshToken = $request->get('refresh_token');
        if (empty($refreshToken)) {
            return $this->errorJson(__('refreshToken不能为空'), \ResponseCode::LOGOUT_ERROR);
        }
        
        if (app('larke.admin.cache')->has(md5($refreshToken))) {
            return $this->errorJson(__('refreshToken已失效'), \ResponseCode::LOGOUT_ERROR);
        }
        
        // 刷新Token
        $refreshJwt = app('larke.admin.jwt')
            ->withJti(config('larkeadmin.passport.refresh_token_id'))
            ->withToken($refreshToken)
            ->decode();
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            return $this->errorJson(__('refreshToken已过期'), \ResponseCode::LOGOUT_ERROR);
        }
        
        $refreshAdminid = $refreshJwt->getClaim('adminid');
        if ($refreshAdminid === false) {
            return $this->errorJson(__('refreshToken错误'), \ResponseCode::LOGOUT_ERROR);
        }
        
        $accessAdminid = app('larke.admin.admin')->getId();
        if ($accessAdminid != $refreshAdminid) {
            return $this->errorJson(__('退出失败'), \ResponseCode::LOGOUT_ERROR);
        }
        
        $accessToken = app('larke.admin.admin')->getAccessToken();
        
        $refreshTokenExpiresIn = $refreshJwt->getClaim('exp') - $refreshJwt->getClaim('iat');
        
        // 添加缓存黑名单
        app('larke.admin.cache')->add(md5($accessToken), 'out', $refreshTokenExpiresIn);
        app('larke.admin.cache')->add(md5($refreshToken), 'out', $refreshTokenExpiresIn);
        
        // 监听事件
        event(new Event\PassportLogoutAfter());
        
        return $this->successJson(__('退出成功'));
    }
    
}
