<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Model\Admin as AdminModel;

// 文件夹引入
use Larke\Admin\Event;

/**
 * 登陆
 *
 * @create 2020-10-19
 * @author deatil
 */
#[RouteRule(
    title: "登陆", 
    desc:  "系统登陆管理",
    order: 100,
    auth:  false,
    slug:  "{prefix}passport"
)]
class Passport extends Base
{
    /**
     * 验证码
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "验证码", 
        desc:  "登陆验证码",
        order: 101,
        auth:  false
    )]
    public function captcha(Request $request)
    {
        $captchaAttr = app('larke-admin.captcha')
            ->makeCode()
            ->getAttr();
        
        $captchaImage = Arr::get($captchaAttr, 'data', '');
        $captchaUniqid = Arr::get($captchaAttr, 'uniq', '');
        
        $captchaKey = config('larkeadmin.passport.header_captcha_key');
        
        // 请求头信息
        $headerExposeHeaders = config('larkeadmin.passport.captcha_expose_headers');
        
        return $this->success(__('larke-admin::common.get_success'), [
            'captcha' => $captchaImage,
        ], [
            "Access-Control-Expose-Headers" => $headerExposeHeaders,
            
            $captchaKey => $captchaUniqid,
        ]);
    }
    
    /**
     * 公钥
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "公钥", 
        desc:  "登陆使用公钥",
        order: 102,
        auth:  false
    )]
    public function passkey(Request $request)
    {
        // 使用 RSA 方法
        $private = RSA::createKey(1024)
            ->withPadding(RSA::ENCRYPTION_PKCS1);
        $public = $private->getPublicKey();
        
        // 私钥
        $privateKey = $private->toString('PKCS8');
        
        // 公钥
        $publicKey = $public->toString('PKCS8');
        
        // 缓存私钥
        $prikeyCacheKey = substr(md5(uniqid().microtime()), 8, 16);
        $prikeyCacheTime = config('larkeadmin.passport.prikey_cache_time');
        Cache::put($prikeyCacheKey, $privateKey, $prikeyCacheTime);
        
        // 过滤公钥多余字符
        $publicKey = str_replace([
            "-----BEGIN PUBLIC KEY-----", 
            "-----END PUBLIC KEY-----", 
            "\r\n",
            "\r",
            "\n",
        ], "", $publicKey);

        $passkeyKey = config('larkeadmin.passport.header_passkey_key');
        
        // 请求头信息
        $headerExposeHeaders = config('larkeadmin.passport.passkey_expose_headers');
        
        return $this->success(__('larke-admin::common.get_success'), [
            'key' => $publicKey,
        ], [
            "Access-Control-Expose-Headers" => $headerExposeHeaders,
            
            $passkeyKey => $prikeyCacheKey,
        ]);
    }
    
    /**
     * 登陆
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "登陆", 
        desc:  "登陆登陆",
        order: 103,
        auth:  false
    )]
    public function login(Request $request)
    {
        // 监听事件
        event(new Event\PassportLoginBefore($request));
        
        $data = $request->all();
        $validator = Validator::make($data, [
            'name'     => 'required',
            'password' => 'required',
            'captcha'  => 'required|size:4',
        ], [
            'name.required'     => __('larke-admin::passport.passport_dont_empty'),
            'password.required' => __('larke-admin::passport.password_dont_empty'),
            'captcha.required'  => __('larke-admin::passport.captcha_dont_empty'),
            'captcha.size'      => __('larke-admin::passport.captcha_error'),
        ]);
        
        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), \ResponseCode::LOGIN_ERROR);
        }
        
        $captchaKey = config('larkeadmin.passport.header_captcha_key');
        $captchaUniq = $request->header($captchaKey);
        $captcha = $request->input('captcha');
        if (! app('larke-admin.captcha')->check($captcha, $captchaUniq)) {
            return $this->error(__('larke-admin::passport.captcha_check_fail'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 校验密码
        $name = $request->input('name');
        $admin = AdminModel::where('name', $name)
            ->first();
        if (empty($admin)) {
            return $this->error(__('larke-admin::passport.passport_dont_exists'), \ResponseCode::LOGIN_ERROR);
        }
        
        $adminInfo = $admin
            ->makeVisible(['password', 'password_salt'])
            ->toArray();
        
        $password = $request->input('password');
        if (strlen($password) <= 16) {
            return $this->error(__('larke-admin::passport.password_error'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 取出 RSA 缓存ID
        $prikeyCacheKey = substr($password, 0, 16);
        
        // 原始密码
        $password = substr($password, 16);

        // 解出密码
        $password = base64_decode($password);
        if (empty($password)) {
            return $this->error(__('larke-admin::passport.password_error'), \ResponseCode::LOGIN_ERROR);
        }

        try {
            // 私钥
            $prikey = Cache::get($prikeyCacheKey);
            
            // 导入私钥
            $rsakey = PublicKeyLoader::load($prikey);
            
            // RSA 解出密码
            $password = $rsakey->withPadding(RSA::ENCRYPTION_PKCS1)
                ->decrypt($password);
        } catch(\Exception $e) {
            Log::error('larke-admin-login: ' . $e->getMessage());
            
            return $this->error(__('larke-admin::passport.password_error'), \ResponseCode::LOGIN_ERROR);
        }

        $encryptPassword = AdminModel::checkPassword($adminInfo, $password); 
        if (! $encryptPassword) {
            event(new Event\PassportLoginPasswordError($admin));
            
            return $this->error(__('larke-admin::passport.password_check_fail'), \ResponseCode::LOGIN_ERROR);
        }
        
        if ($adminInfo['status'] != 1) {
            event(new Event\PassportLoginInactive($admin));
            
            return $this->error(__('larke-admin::passport.passport_disabled_or_not_exists'), \ResponseCode::LOGIN_ERROR);
        }
        
        try {
            // 生成 accessToken
            $accessToken = app('larke-admin.auth-token')
                ->buildAccessToken([
                    'adminid' => $adminInfo['id'],
                ]);
        } catch(\Exception $e) {
            Log::error('larke-admin-login: ' . $e->getMessage());
            
            return $this->error(__('larke-admin::passport.login_fail'), \ResponseCode::LOGIN_ERROR);
        }
        
        if (empty($accessToken)) {
            return $this->error(__('larke-admin::passport.login_fail'), \ResponseCode::LOGIN_ERROR);
        }
        
        try {
            // 刷新token
            $refreshToken = app('larke-admin.auth-token')
                ->buildRefreshToken([
                    'adminid' => $adminInfo['id'],
                ]);
        } catch(\Exception $e) {
            Log::error('larke-admin-login: ' . $e->getMessage());
            
            return $this->error($e->getMessage(), \ResponseCode::LOGIN_ERROR);
        }
        
        if (empty($refreshToken)) {
            return $this->error(__('larke-admin::passport.login_fail'), \ResponseCode::LOGIN_ERROR);
        }
        
        // 清空 RSA 缓存
        Cache::forget($prikeyCacheKey);
        
        // 过期时间
        $expiresIn = app('larke-admin.auth-token')->getAccessTokenExpiresIn();
        
        // 返回数据
        $data = [
            'access_token' => $accessToken,
            'expires_in' => $expiresIn,
            'refresh_token' => $refreshToken,
        ];
        
        // 监听事件
        event(new Event\PassportLoginAfter($admin, $data));
        
        return $this->success(__('larke-admin::passport.login_success'), $data);
    }
    
    /**
     * 刷新token
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "刷新token", 
        desc:  "刷新token",
        order: 104,
        auth:  false
    )]
    public function refreshToken(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        if (empty($refreshToken)) {
            return $this->error(__('larke-admin::passport.refresh_token_dont_empty'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        if (app('larke-admin.cache')->has(md5($refreshToken))) {
            return $this->error(__('larke-admin::passport.refresh_token_timeout'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        try {
            // 旧的刷新token
            $decodeRefreshToken = app('larke-admin.auth-token')
                ->decodeRefreshToken($refreshToken);
            
            // 验证
            app('larke-admin.auth-token')->validate($decodeRefreshToken);
            
            // 签名
            app('larke-admin.auth-token')->verify($decodeRefreshToken);
            
            // 账号ID
            $refreshAdminid = $decodeRefreshToken->getData('adminid');
            
            // 单点登陆处理
            $loginType = config('larkeadmin.passport.login_type', 'many');
            if ($loginType == 'single') {
                $iat = $decodeRefreshToken->getClaim('iat')->getTimestamp();
                
                // 账号信息
                $adminInfo = AdminModel::where('id', $refreshAdminid)
                    ->first();
                if (empty($adminInfo)) {
                    return $this->error(__('larke-admin::auth.passport_error'), \ResponseCode::REFRESH_TOKEN_ERROR);
                }
                
                // 账号信息
                $adminInfo = $adminInfo->toArray();
               
                // 判断是否是单端登陆
                if ($adminInfo['last_active'] != $iat) {
                    return $this->error(__('larke-admin::auth.refresh_token_fail'), \ResponseCode::REFRESH_TOKEN_ERROR);
                }
            }
            
            // 新建access_token
            $newAccessToken = app('larke-admin.auth-token')
                ->buildAccessToken([
                    'adminid' => $refreshAdminid,
                ]);
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::REFRESH_TOKEN_ERROR);
        }

        if (empty($newAccessToken)) {
            return $this->error(__('larke-admin::auth.refresh_token_fail'), \ResponseCode::REFRESH_TOKEN_ERROR);
        }
        
        // 过期时间
        $expiresIn = app('larke-admin.auth-token')->getAccessTokenExpiresIn();
        
        // 返回数据
        $data = [
            'access_token' => $newAccessToken,
            'expires_in' => $expiresIn,
        ];
        
        // 监听事件
        event(new Event\PassportRefreshTokenAfter($data));
        
        return $this->success(__('larke-admin::auth.refresh_token_success'), $data);
    }
    
    /**
     * 退出
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "退出", 
        desc:  "账号退出",
        order: 105,
        auth:  true
    )]
    public function logout(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        if (empty($refreshToken)) {
            return $this->error(__('larke-admin::passport.refresh_token_dont_empty'), \ResponseCode::LOGOUT_ERROR);
        }
        
        if (app('larke-admin.cache')->has(md5($refreshToken))) {
            return $this->error(__('larke-admin::passport.refresh_token_timeout'), \ResponseCode::LOGOUT_ERROR);
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
            $refreshTokenExpiresIn = $decodeRefreshToken->getClaim('exp')->getTimestamp() - $decodeRefreshToken->getClaim('iat')->getTimestamp();
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), \ResponseCode::LOGOUT_ERROR);
        }
        
        $accessAdminid = app('larke-admin.auth-admin')->getId();
        if ($accessAdminid != $refreshAdminid) {
            return $this->error(__('larke-admin::passport.logout_fail'), \ResponseCode::LOGOUT_ERROR);
        }
        
        $accessToken = app('larke-admin.auth-admin')->getAccessToken();
        
        // 添加缓存黑名单
        app('larke-admin.cache')->add(md5($accessToken), time(), $refreshTokenExpiresIn);
        app('larke-admin.cache')->add(md5($refreshToken), time(), $refreshTokenExpiresIn);
        
        // 监听事件
        event(new Event\PassportLogoutAfter([
            'access_token' => $accessToken,
            'expires_in' => $refreshTokenExpiresIn,
            'refresh_token' => $refreshToken,
        ]));
        
        return $this->success(__('larke-admin::passport.logout_success'));
    }
    
}
