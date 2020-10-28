<?php

namespace Larke\Admin\Middleware;

use Closure;

/*
 * 权限检测
 *
 * @create 2020-10-28
 * @author deatil
 */
class Permission
{
    public function handle($request, Closure $next)
    {
        
        return $next($request);
    }

}
