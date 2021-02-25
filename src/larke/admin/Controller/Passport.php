<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Support\Password;
use Larke\Admin\Model\Admin as AdminModel;

// 文件夹引入
use Larke\Admin\Event;

/**
 * 登陆
 *
 * @title 登陆
 * @desc 系统登陆管理
 * @order 100
 * @auth false
 * @slug larke-admin.passport
 *
 * @create 2020-10-19
 * @author deatil
 */
class Passport extends Base
{
    /**
     * 验证码
     *
     * @title 验证码
     * @desc 登陆验证码
     * @order 101
     * @auth false
     *
     * @param  Request  $request
     * @return Response
     */
    public function captcha(Request $request)
    {
        $captchaAttr = app('larke-admin.captcha')
            ->makeCode()
            ->getAttr();
        
        $captchaImage = Arr::get($captchaAttr, 'data', '');
        $captchaUniqid = Arr::get($captchaAttr, 'uniq', '');
        
        $captchaKey = config('larkeadmin.passport.header_captcha_key');
        return $this->success(__('获取成功'), [
            'captcha' => $captchaImage,
        ], 0, [
            $captchaKey => $captchaUniqid,
        ]);
    }
    
    /**
     * 登陆
     *
     * @title 登陆
     * @desc 登陆登陆
     * @order 102
     * @auth false
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
            return $this->error($validator->errors()->first(), \ResponseCode::LOGIN_ERROR);
        }
        
        $captchaKey = config('larkeadmin.passport.header_captcha_key');
        $captchaUniq = $request->header($captchaKey);
        $captcha = $request->input('captcha');
        if (! app('larke-admin.captcha')->check($captcha, $captchaUniq)) {
            return $this->error(__('验证码错误'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 校验密码
        $name = $request->input('name');
        $admin = AdminModel::where('name', $name)
            ->first();
        if (empty($admin)) {
            return $this->error(__('帐号错误'), \ResponseCode::LOGIN_ERROR);
        }
        
        $adminInfo = $admin->toArray();
        $password = $request->input('password');
        
        $encryptPassword = (new Password())
            ->withSalt(config('larkeadmin.passport.password_salt'))
            ->encrypt($password, $adminInfo['password_salt']); 
        if ($encryptPassword != $adminInfo['password']) {
            event(new Event\PassportLoginPasswordError($admin));
            
            return $this->error(__('账号密码错误'), \ResponseCode::LOGIN_ERROR);
        }
        
        if ($adminInfo['status'] == 0) {
            event(new Event\PassportLoginInactive($admin));
            
            return $this->error(__('用户已被禁用或者不存在'), \ResponseCode::LOGIN_ERROR);
        }
        
        try {
            // 生成 accessToken
            $expiresIn = config('larkeadmin.passport.access_expires_in', 86400);
            $accessToken = app('larke-admin.jwt')
                ->withData([
                    'adminid' => $adminInfo['id'],
                ])
                ->withExp($expiresIn)
                ->withJti(config('larkeadmin.passport.access_token_id'))
                ->encode()
                ->getToken();
            if (empty($accessToken)) {
                return $this->error(__('登陆失败'), \ResponseCode::LOGIN_ERROR);
            }
            
            // 刷新token
            $refreshExpiresIn = config('larkeadmin.passport.refresh_expires_in', 300);
            $refreshToken = app('larke-admin.jwt')
                ->withData([
                    'adminid' => $adminInfo['id'],
                ])
                ->withExp($refreshExpiresIn)
                ->withJti(config('larkeadmin.passport.refresh_token_id'))
                ->encode()
                ->getToken();
            if (empty($refreshToken)) {
                return $this->error(__('登陆失败'), \ResponseCode::LOGIN_ERROR);
            }
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::LOGIN_ERROR);
        }
        
        // 监听事件
        event(new Event\PassportLoginAfter($admin));
        
        return $this->success(__('登录成功'), [
            'access_token' => $accessToken,
            'expires_in' => $expiresIn, // 过期时间
            'refresh_token' => $refreshToken,
        ]);
    }
    
    /**
     * 刷新token
     *
     * @title 刷新token
     * @desc 刷新token
     * @order 103
     * @auth false
     *
     * @param  Request  $request
     * @return Response
     */
    public function refreshToken(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        if (empty($refreshToken)) {
            return $this->error(__('refreshToken不能为空'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        if (app('larke-admin.cache')->has(md5($refreshToken))) {
            return $this->error(__('refreshToken已失效'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        try {
            // 旧的刷新token
            $refreshJwt = app('larke-admin.jwt')
                ->withJti(config('larkeadmin.passport.refresh_token_id'))
                ->withToken($refreshToken)
                ->decode();
            
            if (!($refreshJwt->validate() && $refreshJwt->verify())) {
                return $this->error(__('refreshToken已过期'), \ResponseCode::REFRESH_TOKEN_TIMEOUT);
            }
            
            $refreshAdminid = $refreshJwt->getData('adminid');
            
            // 新建access_token
            $expiresIn = config('larkeadmin.passport.access_expires_in', 86400);
            $newAccessToken = app('larke-admin.jwt')
                ->withData([
                    'adminid' => $refreshAdminid,
                ])
                ->withExp($expiresIn)
                ->withJti(config('larkeadmin.passport.access_token_id'))
                ->encode()
                ->getToken();
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::REFRESH_TOKEN_ERROR);
        }

        if (empty($newAccessToken)) {
            return $this->error(__('刷新Token失败'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        // 监听事件
        event(new Event\PassportRefreshTokenAfter());
        
        return $this->success(__('刷新Token成功'), [
            'access_token' => $newAccessToken,
            'expires_in' => $expiresIn,
        ]);
    }
    
    /**
     * 退出
     *
     * @title 退出
     * @desc 退出
     * @order 104
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function logout(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        if (empty($refreshToken)) {
            return $this->error(__('refreshToken不能为空'), \ResponseCode::LOGOUT_ERROR);
        }
        
        if (app('larke-admin.cache')->has(md5($refreshToken))) {
            return $this->error(__('refreshToken已失效'), \ResponseCode::LOGOUT_ERROR);
        }
        
        try {
            // 刷新Token
            $refreshJwt = app('larke-admin.jwt')
                ->withJti(config('larkeadmin.passport.refresh_token_id'))
                ->withToken($refreshToken)
                ->decode();
            
            if (! ($refreshJwt->validate() && $refreshJwt->verify())) {
                return $this->error(__('refreshToken已过期'), \ResponseCode::LOGOUT_ERROR);
            }
            
            $refreshAdminid = $refreshJwt->getData('adminid');
            
            // 过期时间
            $refreshTokenExpiresIn = $refreshJwt->getClaim('exp') - $refreshJwt->getClaim('iat');
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::LOGOUT_ERROR);
        }
        
        $accessAdminid = app('larke-admin.admin')->getId();
        if ($accessAdminid != $refreshAdminid) {
            return $this->error(__('退出失败'), \ResponseCode::LOGOUT_ERROR);
        }
        
        $accessToken = app('larke-admin.admin')->getAccessToken();
        
        // 添加缓存黑名单
        app('larke-admin.cache')->add(md5($accessToken), time(), $refreshTokenExpiresIn);
        app('larke-admin.cache')->add(md5($refreshToken), time(), $refreshTokenExpiresIn);
        
        // 监听事件
        event(new Event\PassportLogoutAfter());
        
        return $this->success(__('退出成功'));
    }
    
}
