<?php

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

/*
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
        $isAdministrator = app('larke.admin')->isAdministrator();
        if (!$isAdministrator) {
            $this->errorJson(__('你没有权限进行该操作'), \ResponseCode::AUTH_ERROR);
        }
        
        return $next($request);
    }

}
