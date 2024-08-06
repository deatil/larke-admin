<?php

declare (strict_types = 1);

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

/**
 * 超级管理员检测
 *
 * @create 2020-10-28
 * @author deatil
 */
class AdminCheck
{
    use ResponseJsonTrait;
    
    public function handle($request, Closure $next)
    {
        $isSuperAdministrator = app('larke-admin.auth-admin')->isSuperAdministrator();
        if (! $isSuperAdministrator) {
            return $this->error(__('larke-admin::auth.no_permission'), \ResponseCode::AUTH_ERROR);
        }
        
        return $next($request);
    }

}
