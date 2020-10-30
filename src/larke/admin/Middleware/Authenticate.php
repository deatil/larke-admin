<?php

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Traits\Json as JsonTrait;
use Larke\Admin\Model\Admin as AdminModel;

/*
 * jwt 验证
 *
 * @create 2020-10-19
 * @author deatil
 */
class Authenticate
{
    use JsonTrait;
    
    public function handle($request, Closure $next)
    {
        if (!$this->shouldPassThrough($request)) {
            $this->jwtCheck();
        }
        
        return $next($request);
    }
    
    /*
     * jwt验证
     */
    protected function jwtCheck()
    {
        $authorization = request()->header('Authorization');
        if (!$authorization) {
            $this->errorJson(__('token不能为空'));
        }
        
        $authorizationArr = explode(' ', $authorization);
        if (count($authorizationArr) != 2) {
            $this->errorJson(__('token不能为空'));
        }
        if ($authorizationArr[0] != 'Bearer') {
            $this->errorJson(__('token格式错误'));
        }
        
        $token = $authorizationArr[1];
        if (!$token) {
            $this->errorJson(__('token不能为空'));
        }
        
        if (count(explode('.', $token)) <> 3) {
            $this->errorJson(__('token格式错误'));
        }
        
        if (app('larke.cache')->has(md5($token))) {
            $this->errorJson(__('token已失效'));
        }
        
        $jwtAuth = app('larke.jwt')
            ->withJti(config('larke.passport.access_token_id'))
            ->withToken($token)
            ->decode();
        
        if (!($jwtAuth->validate() && $jwtAuth->verify())) {
            $this->errorJson(__('token已过期'));
        }
        
        $adminid = $jwtAuth->getClaim('adminid');
        if ($adminid === false) {
            $this->errorJson(__('token错误'));
        }
        
        $adminInfo = AdminModel::where('id', $adminid)
            ->with(['groups'])
            ->first();
        if (empty($adminInfo)) {
            $this->errorJson(__('帐号不存在或者已被锁定'));
        }
        
        $adminInfo = $adminInfo->toArray();
        if ($adminInfo['status'] != 1) {
            $this->errorJson(__('帐号不存在或者已被锁定'));
        }
        
        app('larke.admin')->withId($adminid)
            ->withData($adminInfo);
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $excepts = array_merge(config('larke.auth.excepts', []), [
            'larke-admin-passport-captcha',
            'larke-admin-passport-login',
            'larke-admin-passport-refresh-token',
            'larke-admin-attachment-download',
        ]);

        return collect($excepts)
            ->contains(function ($except) {
                $requestUrl = \Route::currentRouteName();
                return ($except == $requestUrl);
            });
    }

}
