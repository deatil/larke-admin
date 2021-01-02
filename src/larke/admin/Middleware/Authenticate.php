<?php

declare (strict_types = 1);

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Service\Route as RouteService;
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
        
        if (app('larke.admin.cache')->has(md5($accessToken))) {
            $this->error(__('token已失效'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $jwtAuth = app('larke.admin.jwt')
            ->withJti(config('larkeadmin.passport.access_token_id'))
            ->withToken($accessToken)
            ->decode();
        
        if (!($jwtAuth->validate() && $jwtAuth->verify())) {
            $this->error(__('token已过期'), \ResponseCode::ACCESS_TOKEN_TIMEOUT);
        }
        
        $adminid = $jwtAuth->getData('adminid');
        if ($adminid === false) {
            $this->error(__('token错误'), \ResponseCode::ACCESS_TOKEN_ERROR);
        }
        
        $adminInfo = AdminModel::where('id', $adminid)
            ->with(['groups'])
            ->first();
        if (empty($adminInfo)) {
            $this->error(__('帐号不存在或者已被锁定'), \ResponseCode::AUTH_ERROR);
        }
        
        $adminInfo = $adminInfo->toArray();
        
        app('larke.admin.admin')
            ->withAccessToken($accessToken)
            ->withId($adminid)
            ->withData($adminInfo);
        
        if (! app('larke.admin.admin')->isActive()) {
            $this->error(__('帐号不存在或者已被锁定'), \ResponseCode::AUTH_ERROR);
        }
        
        if (! app('larke.admin.admin')->isGroupActive()) {
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
        $excepts = array_merge(config('larkeadmin.auth.excepts', []), [
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
