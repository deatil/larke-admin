<?php

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;
use Larke\Admin\Model\AuthRule as AuthRuleModel;

/*
 * 权限检测
 *
 * @create 2020-10-28
 * @author deatil
 */
class Permission
{
    use ResponseJsonTrait;
    
    public function handle($request, Closure $next)
    {
        if (!$this->shouldPassThrough($request)) {
            $this->permissionCheck();
        }
        
        return $next($request);
    }
    
    /*
     * 权限检测
     */
    public function permissionCheck()
    {
        if (app('larke.admin.admin')->isAdministrator()) {
            return;
        }
        
        $adminId = app('larke.admin.admin')->getId();
        $requestUrl = \Route::currentRouteName();
        $requestMethod = request()->getMethod();
        
        if (!\Enforcer::enforce($adminId, $requestUrl, $requestMethod)) {
            $this->errorJson(__('你没有访问权限'), \ResponseCode::AUTH_ERROR);
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
            'larke-admin-passport-captcha',
            'larke-admin-passport-login',
            'larke-admin-passport-refresh-token',
            'larke-admin-attachment-download',
        ]);
        
        $excepts = array_merge($excepts, $this->shouldPassSlugs());
        
        return collect($excepts)
            ->contains(function ($except) {
                $requestUrl = \Route::currentRouteName();
                return ($except == $requestUrl);
            });
    }
    
    /**
     * 需要过滤的Slug列表
     *
     * @return array|null
     */
    protected function shouldPassSlugs()
    {
        $rules = AuthRuleModel::getAuthRules();
        
        $ruleSlugs = collect($rules)->map(function($data) {
            if ($data['is_need_auth'] == 0) {
                return $data['slug'];
            }
            
            return null;
        })->filter(function($data) {
            return !empty($data);
        })->toArray();
        
        return $ruleSlugs;
    }

}
