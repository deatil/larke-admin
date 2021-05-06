<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

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
 * @slug {prefix}passport
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
        ], [
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
        
        $adminInfo = $admin
            ->makeVisible(['password', 'password_salt'])
            ->toArray();
        $password = $request->input('password');
        
        $encryptPassword = AdminModel::checkPassword($adminInfo, $password); 
        if (! $encryptPassword) {
            event(new Event\PassportLoginPasswordError($admin));
            
            return $this->error(__('账号密码错误'), \ResponseCode::LOGIN_ERROR);
        }
        
        if ($adminInfo['status'] != 1) {
            event(new Event\PassportLoginInactive($admin));
            
            return $this->error(__('用户已被禁用或者不存在'), \ResponseCode::LOGIN_ERROR);
        }
        
        try {
            // 生成 accessToken
            $accessToken = app('larke-admin.auth-token')
                ->buildAccessToken([
                    'adminid' => $adminInfo['id'],
                ]);
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::LOGIN_ERROR);
        }
        
        if (empty($accessToken)) {
            return $this->error(__('登陆失败'), \ResponseCode::LOGIN_ERROR);
        }
        
        try {
            // 刷新token
            $refreshToken = app('larke-admin.auth-token')
                ->buildRefreshToken([
                    'adminid' => $adminInfo['id'],
                ]);
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::LOGIN_ERROR);
        }
        
        if (empty($refreshToken)) {
            return $this->error(__('登陆失败'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 监听事件
        event(new Event\PassportLoginAfter($admin));
        
        // 过期时间
        $expiresIn = app('larke-admin.auth-token')->getAccessTokenExpiresIn();
        
        return $this->success(__('登录成功'), [
            'access_token' => $accessToken,
            'expires_in' => $expiresIn,
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
            $decodeRefreshToken = app('larke-admin.auth-token')
                ->decodeRefreshToken($refreshToken);
            
            // 验证
            app('larke-admin.auth-token')->validate($decodeRefreshToken);
            
            // 签名
            app('larke-admin.auth-token')->verify($decodeRefreshToken);
            
            $refreshAdminid = $decodeRefreshToken->getData('adminid');
            
            // 新建access_token
            $newAccessToken = app('larke-admin.auth-token')
                ->buildAccessToken([
                    'adminid' => $refreshAdminid,
                ]);
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::REFRESH_TOKEN_ERROR);
        }

        if (empty($newAccessToken)) {
            return $this->error(__('刷新Token失败'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        // 监听事件
        event(new Event\PassportRefreshTokenAfter());
        
        // 过期时间
        $expiresIn = app('larke-admin.auth-token')->getAccessTokenExpiresIn();
        
        return $this->success(__('刷新Token成功'), [
            'access_token' => $newAccessToken,
            'expires_in' => $expiresIn,
        ]);
    }
    
    /**
     * 退出
     *
     * @title 退出
     * @desc 账号退出
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
            $decodeRefreshToken = app('larke-admin.auth-token')
                ->decodeRefreshToken($refreshToken);
            
            // 验证
            app('larke-admin.auth-token')->validate($decodeRefreshToken);
            
            // 签名
            app('larke-admin.auth-token')->verify($decodeRefreshToken);
            
            $refreshAdminid = $decodeRefreshToken->getData('adminid');
            
            // 过期时间
            $refreshTokenExpiresIn = $decodeRefreshToken->getClaim('exp') - $decodeRefreshToken->getClaim('iat');
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::LOGOUT_ERROR);
        }
        
        $accessAdminid = app('larke-admin.auth-admin')->getId();
        if ($accessAdminid != $refreshAdminid) {
            return $this->error(__('退出失败'), \ResponseCode::LOGOUT_ERROR);
        }
        
        $accessToken = app('larke-admin.auth-admin')->getAccessToken();
        
        // 添加缓存黑名单
        app('larke-admin.cache')->add(md5($accessToken), time(), $refreshTokenExpiresIn);
        app('larke-admin.cache')->add(md5($refreshToken), time(), $refreshTokenExpiresIn);
        
        // 监听事件
        event(new Event\PassportLogoutAfter());
        
        return $this->success(__('退出成功'));
    }
    
}
