<?php

declare (strict_types = 1);

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Service\Route as RouteService;
use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

/**
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
        if (! $this->shouldPassThrough($request)) {
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
            $this->error(__('token不能为空'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $authorizationArr = explode(' ', $authorization);
        if (count($authorizationArr) != 2) {
            $this->error(__('token不能为空'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        if ($authorizationArr[0] != 'Bearer') {
            $this->error(__('token格式错误'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $accessToken = $authorizationArr[1];
        if (!$accessToken) {
            $this->error(__('token不能为空'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        if (count(explode('.', $accessToken)) <> 3) {
            $this->error(__('token格式错误'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        if (app('larke-admin.cache')->has(md5($accessToken))) {
            $this->error(__('token已失效'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        try {
            $decodeAccessToken = app('larke-admin.auth-token')
                ->decodeAccessToken($accessToken);
        } catch(\Exception $e) {
            $this->error(__('token格式错误'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        try {
            // 验证
            app('larke-admin.auth-token')->validate($decodeAccessToken);
            
            // 签名
            app('larke-admin.auth-token')->verify($decodeAccessToken);
        } catch(\Exception $e) {
            $this->error(__('token已过期'), \ResponseCode::ACCESS_TOKEN_TIMEOUT);
        }
        
        try {
            $adminid = $decodeAccessToken->getData('adminid');
        } catch(\Exception $e) {
            $this->error(__('token已失效'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $adminInfo = AdminModel::where('id', $adminid)
            ->with(['groups'])
            ->first();
        if (empty($adminInfo)) {
            $this->error(__('帐号不存在或者已被锁定'), \ResponseCode::AUTH_ERROR);
        }
        
        // 账号信息
        $adminInfo = $adminInfo->toArray();
        
        // 单点登陆处理
        $loginType = config('larkeadmin.passport.login_type', 'many');
        if ($loginType == 'single') {
            $iat = $decodeAccessToken->getClaim('iat');
            
            // 判断是否是单点登陆
            if ($adminInfo['refresh_time'] != $iat) {
                return $this->error(__('token已失效'), \ResponseCode::ACCESS_TOKEN_TIMEOUT);
            }
        }
        
        app('larke-admin.auth-admin')
            ->withAccessToken($accessToken)
            ->withId($adminid)
            ->withData($adminInfo);
        
        if (! app('larke-admin.auth-admin')->isActive()) {
            $this->error(__('帐号不存在或者已被锁定'), \ResponseCode::AUTH_ERROR);
        }
        
        if (! app('larke-admin.auth-admin')->isGroupActive()) {
            $this->error(__('帐号用户组不存在或者已被锁定'), \ResponseCode::AUTH_ERROR);
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
        $excepts = array_merge(config('larkeadmin.auth.authenticate_excepts', []), [
            $this->formatRouteSlug('system.set-lang'),
            $this->formatRouteSlug('passport.passkey'),
            $this->formatRouteSlug('passport.captcha'),
            $this->formatRouteSlug('passport.login'),
            $this->formatRouteSlug('passport.refresh-token'),
            $this->formatRouteSlug('attachment.download'),
        ]);
        
        return collect($excepts)
            ->contains(function ($except) {
                $requestUrl = \Route::currentRouteName();
                return ($except == $requestUrl);
            });
    }
    
    /**
     * 格式化路由标识
     */
    protected function formatRouteSlug($slug = '')
    {
        return RouteService::formatRouteSlug($slug);
    }

}
