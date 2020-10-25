<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Captcha\Captcha;
use Larke\Admin\Service\Password as PasswordService;
use Larke\Admin\Model\Admin as AdminModel;

/**
 * 登陆
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
        
        $validator = Validator::make($data, [
            'id' => 'required|alpha_num|size:32',
        ], [
            'id.required' => __('ID不能为空'),
            'id.alpha_num' => __('ID格式错误'),
            'id.size' => __('ID长度格式错误'),
        ]);
        
        if ($validator->fails()) {
            $this->errorJson($validator->errors()->first());
        }
        
        $id = $request->get('id');
        
        $Captcha = new Captcha([
            'uniqid' => $id,
        ]);
        
        $captcha = $Captcha->getData();
        
        $this->successJson(__('获取成功'), [
            'captcha' => $captcha,
        ]);
    }
    
    /**
     * 登陆
     */
    public function login(Request $request)
    {
        $name = $request->get('name');
        if (empty($name)) {
            $this->errorJson(__('账号不能为空'));
        }

        $password = $request->post('password');
        if (empty($password)) {
            $this->errorJson(__('密码不能为空'));
        }
        if (strlen($password) != 32) {
            $this->errorJson(__('用户密码错误'));
        }
        
        $captcha = $request->get('captcha');
        if (empty($captcha)) {
            $this->errorJson(__('验证码不能为空'));
        }
        if (!Captcha::check($captcha, md5($name))) {
            $this->errorJson(__('验证码错误'));
        }
        
        // 校验密码
        $adminInfo = AdminModel::where('name', $name)
            ->first();
        if (empty($adminInfo)) {
            $this->errorJson(__('帐号错误'));
        }
        
        $adminInfo = $adminInfo->toArray();
        
        $password2 = (new PasswordService())
            ->withSalt(config('larke.passport.salt'))
            ->encrypt($password, $adminInfo['passport_salt']); 
        if ($password2 != $adminInfo['password']) {
            $this->errorJson(__('账号密码错误'));
        }
        
        if ($adminInfo['status'] == 0) {
            $this->errorJson(__('用户已被禁用或者不存在'));
        }
        
        // 获取jwt的句柄
        $expiredIn = config('larke.passport.expired_in', 86400);
        $accessToken = app('larke.jwt')->withClaim([
            'adminid' => $adminInfo['id'],
        ])->withExpTime($expiredIn)
            ->withJti(config('larke.passport.access_token_id'))
            ->encode()
            ->getToken();
        if (empty($accessToken)) {
            $this->errorJson(__('登陆失败'));
        }
        
        // 刷新token
        $refreshExpiredIn = config('larke.passport.refresh_expired_in', 300);
        $refreshToken = app('larke.jwt')->withClaim([
            'adminid' => $adminInfo['id'],
            'expired_in' => $refreshExpiredIn,
        ])->withExpTime($refreshExpiredIn)
            ->withJti(config('larke.passport.refresh_token_id'))
            ->encode()
            ->getToken();
        if (empty($refreshToken)) {
            $this->errorJson(__('登陆失败'));
        }
        
        // 更新信息
        AdminModel::where('id', $adminInfo['id'])->update([
            'last_active' => time(), 
            'last_ip' => $request->ip(),
        ]);
        
        $this->successJson(__('登录成功'), [
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
        $accessToken = $request->get('access_token');
        if (empty($accessToken)) {
            $this->errorJson(__('accessToken不能为空'));
        }
        
        $refreshToken = $request->get('refresh_token');
        if (empty($refreshToken)) {
            $this->errorJson(__('refreshToken不能为空'));
        }
        
        if (Cache::has(md5($refreshToken))) {
            $this->errorJson(__('refreshToken已失效'));
        }
        
        // accessToken
        $accessJwt = app('larke.jwt')
            ->withJti(config('larke.passport.access_token_id'));
            
        try {
            $accessJwt->withToken($accessToken)->decode();
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if (!$accessJwt->verify()) {
            $this->errorJson(__('accessToken错误'));
        }
        
        try {
            $accessAdminid = $accessJwt->getClaim('adminid');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        $refreshJwt = app('larke.jwt')
            ->withJti(config('larke.passport.refresh_token_id'));
        
        try {
            $refreshJwt->withToken($refreshToken)->decode();
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            $this->errorJson(__('token已过期'));
        }
        
        try {
            $refreshAdminid = $refreshJwt->getClaim('adminid');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        try {
            $refreshTokenExpiredIn = $refreshJwt->getClaim('expired_in');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if ($accessAdminid != $refreshAdminid) {
            $this->errorJson(__('刷新Token失败'));
        }
        
        $expiredIn = config('larke.passport.expired_in', 300);
        $newAccessToken = app('larke.jwt')->withClaim([
            'adminid' => $refreshAdminid,
        ])->withExpTime($expiredIn)
            ->withJti(config('larke.passport.refresh_token_id'))
            ->encode()
            ->getToken();
        if (empty($newAccessToken)) {
            $this->errorJson(__('刷新Token失败'));
        }
        
        // 添加缓存黑名单
        Cache::add(md5($accessToken), $accessToken, $refreshTokenExpiredIn);
        
        $this->successJson(__('刷新Token成功'), [
            'access_token' => $newAccessToken,
            'expired_in' => $expiredIn,
        ]);
    }
    
    /**
     * 退出
     */
    public function logout(Request $request)
    {
        $accessToken = $request->get('access_token');
        if (empty($accessToken)) {
            $this->errorJson(__('accessToken不能为空'));
        }
        
        $refreshToken = $request->get('refresh_token');
        if (empty($refreshToken)) {
            $this->errorJson(__('refreshToken不能为空'));
        }
        
        if (Cache::has(md5($refreshToken))) {
            $this->errorJson(__('refreshToken已失效'));
        }
        
        // accessToken
        $accessJwt = app('larke.jwt')->withJti(config('larke.passport.access_token_id'));
        try {
            $accessJwt->withToken($accessToken)->decode();
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        if (!($accessJwt->validate() && $accessJwt->verify())) {
            $this->errorJson(__('accessToken已过期'));
        }
        try {
            $accessAdminid = $accessJwt->getClaim('adminid');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        // 刷新Token
        $refreshJwt = app('larke.jwt')->withJti(config('larke.passport.refresh_token_id'));
        
        try {
            $refreshJwt->withToken($refreshToken)->decode();
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            $this->errorJson(__('refreshToken已过期'));
        }
        
        try {
            $refreshAdminid = $refreshJwt->getClaim('adminid');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        try {
            $refreshTokenExpiredIn = $refreshJwt->getClaim('expired_in');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if ($accessAdminid != $refreshAdminid) {
            $this->errorJson(__('退出失败'));
        }
        
        // 添加缓存黑名单
        Cache::add(md5($accessToken), $accessToken, $refreshTokenExpiredIn);
        Cache::add(md5($refreshToken), $refreshToken, $refreshTokenExpiredIn);
        
        $this->successJson(__('退出成功'));
    }
    
}
