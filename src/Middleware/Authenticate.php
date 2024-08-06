<?php

declare (strict_types = 1);

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Service\Route as RouteService;
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
            if (($res = $this->jwtCheck()) !== null) {
                return $res;
            }
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
            return $this->error(__('larke-admin::auth.token_dont_empty'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $authorizationArr = explode(' ', $authorization);
        if (count($authorizationArr) != 2) {
            return $this->error(__('larke-admin::auth.token_dont_empty'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        if ($authorizationArr[0] != 'Bearer') {
            return $this->error(__('larke-admin::auth.token_error'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $accessToken = $authorizationArr[1];
        if (!$accessToken) {
            return $this->error(__('larke-admin::auth.token_dont_empty'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        if (count(explode('.', $accessToken)) <> 3) {
            return $this->error(__('larke-admin::auth.token_error'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        if (app('larke-admin.cache')->has(md5($accessToken))) {
            return $this->error(__('larke-admin::auth.token_no_use'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        try {
            $decodeAccessToken = app('larke-admin.auth-token')
                ->decodeAccessToken($accessToken);
        } catch(\Exception $e) {
            return $this->error(__('larke-admin::auth.token_error'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        try {
            // 验证
            app('larke-admin.auth-token')->validate($decodeAccessToken);
            
            // 签名
            app('larke-admin.auth-token')->verify($decodeAccessToken);
        } catch(\Exception $e) {
            return $this->error(__('larke-admin::auth.token_timeout'), \ResponseCode::ACCESS_TOKEN_TIMEOUT);
        }
        
        try {
            $adminid = $decodeAccessToken->getData('adminid');
        } catch(\Exception $e) {
            return $this->error(__('larke-admin::auth.token_no_use'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $adminInfo = AdminModel::where('id', $adminid)
            ->with(['groups'])
            ->first();
        if (empty($adminInfo)) {
            return $this->error(__('larke-admin::auth.passport_error'), \ResponseCode::AUTH_ERROR);
        }
        
        // 账号信息
        $adminInfo = $adminInfo->toArray();
        
        app('larke-admin.auth-admin')
            ->withAccessToken($accessToken)
            ->withId($adminid)
            ->withData($adminInfo);
        
        if (! app('larke-admin.auth-admin')->isActive()) {
            return $this->error(__('larke-admin::auth.passport_error'), \ResponseCode::AUTH_ERROR);
        }
        
        if (! app('larke-admin.auth-admin')->isGroupActive()) {
            return $this->error(__('larke-admin::auth.group_error'), \ResponseCode::AUTH_ERROR);
        }
        
        return null;
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
