<?php

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

/*
 * jwt 验证
 *
 * @create 2020-10-19
 * @author deatil
 */
class Authenticate
{
    use ResponseJsonTrait;
    
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
            $this->errorJson(__('token不能为空'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $authorizationArr = explode(' ', $authorization);
        if (count($authorizationArr) != 2) {
            $this->errorJson(__('token不能为空'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        if ($authorizationArr[0] != 'Bearer') {
            $this->errorJson(__('token格式错误'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $accessToken = $authorizationArr[1];
        if (!$accessToken) {
            $this->errorJson(__('token不能为空'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        if (count(explode('.', $accessToken)) <> 3) {
            $this->errorJson(__('token格式错误'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        if (app('larke.cache')->has(md5($accessToken))) {
            $this->errorJson(__('token已失效'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $jwtAuth = app('larke.jwt')
            ->withJti(config('larke.passport.access_token_id'))
            ->withToken($accessToken)
            ->decode();
        
        if (!($jwtAuth->validate() && $jwtAuth->verify())) {
            $this->errorJson(__('token已过期'), \ResponseCode::ACCESS_TOKEN_TIMEOUT);
        }
        
        $adminid = $jwtAuth->getClaim('adminid');
        if ($adminid === false) {
            $this->errorJson(__('token错误'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $adminInfo = AdminModel::where('id', $adminid)
            ->with(['groups'])
            ->first();
        if (empty($adminInfo)) {
            $this->errorJson(__('帐号不存在或者已被锁定'), \ResponseCode::AUTH_ERROR);
        }
        
        $adminInfo = $adminInfo->toArray();
        
        app('larke.admin')
            ->withAccessToken($accessToken)
            ->withId($adminid)
            ->withData($adminInfo);
        
        if (! app('larke.admin')->isActive()) {
            $this->errorJson(__('帐号不存在或者已被锁定'), \ResponseCode::AUTH_ERROR);
        }
        
        if (! app('larke.admin')->isGroupActive()) {
            $this->errorJson(__('帐号用户组不存在或者已被锁定'), \ResponseCode::AUTH_ERROR);
        }
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
