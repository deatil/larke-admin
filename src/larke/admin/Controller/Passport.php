<?php

namespace Larke\Admin\Controller;

use Illuminate\Support\Facades\Cache;

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
     * 登陆
     */
    public function login()
    {
        $name = request()->get('name');
        if (empty($name)) {
            $this->errorJson(__('账号不能为空'));
        }

        $password = request()->post('password');
        if (empty($password)) {
            $this->errorJson(__('密码不能为空'));
        }
        if (strlen($password) != 32) {
            $this->errorJson(__('用户密码错误'));
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
            ->encode()
            ->getToken();
        if (empty($refreshToken)) {
            $this->errorJson(__('登陆失败'));
        }
        
        // 更新信息
        AdminModel::where('id', $adminInfo['id'])->update([
            'last_active' => time(), 
            'last_ip' => request()->ip(),
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
    public function refreshToken()
    {
        $accessToken = request()->get('access_token');
        if (empty($accessToken)) {
            $this->errorJson(__('accessToken不能为空'));
        }
        
        $refreshToken = request()->get('refresh_token');
        if (empty($refreshToken)) {
            $this->errorJson(__('refreshToken不能为空'));
        }
        
        if (Cache::has(md5($refreshToken))) {
            $this->errorJson(__('refreshToken已失效'));
        }
        
        $jwtAuth = app('larke.jwt');
        
        try {
            $jwtAuth->withToken($refreshToken)->decode();
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if (!($jwtAuth->validate() && $jwtAuth->verify())) {
            $this->errorJson(__('token已过期'));
        }
        
        try {
            $adminid = $jwtAuth->getClaim('adminid');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        try {
            $refreshTokenExpiredIn = $jwtAuth->getClaim('expired_in');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        $expiredIn = config('larke.passport.expired_in', 300);
        $newAccessToken = app('larke.jwt')->withClaim([
            'adminid' => $adminid,
        ])->withExpTime($expiredIn)
            ->encode()
            ->getToken();
        if (empty($newAccessToken)) {
            $this->errorJson(__('刷新Token失败'));
        }
        
        // 添加缓存黑名单
        Cache::put(md5($accessToken), $accessToken, $refreshTokenExpiredIn);
        
        $this->successJson(__('刷新Token成功'), [
            'access_token' => $newAccessToken,
            'expired_in' => $expiredIn,
        ]);
    }
    
    /**
     * 退出
     */
    public function logout()
    {
        $accessToken = request()->get('access_token');
        if (empty($accessToken)) {
            $this->errorJson(__('accessToken不能为空'));
        }
        
        $refreshToken = request()->get('refresh_token');
        if (empty($refreshToken)) {
            $this->errorJson(__('refreshToken不能为空'));
        }
        
        if (Cache::has(md5($refreshToken))) {
            $this->errorJson(__('refreshToken已失效'));
        }
        
        // accessToken
        $accessJwt = app('larke.jwt');
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
        $refreshJwt = app('larke.jwt');
        
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
        Cache::put(md5($accessToken), $accessToken, $refreshTokenExpiredIn);
        Cache::put(md5($refreshToken), $refreshToken, $refreshTokenExpiredIn);
        
        $this->successJson(__('退出成功'));
    }
    
}
