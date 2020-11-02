<?php

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Model\AdminLog as AdminLogModel;

/*
 * 日志
 *
 * @create 2020-10-21
 * @author deatil
 */
class Log
{
    public function handle($request, Closure $next)
    {
        $adminInfo = app('larke.admin')->getData();
        
        $response = $next($request);
        
        $input = $request->except(['password', 'password2', 'password_salt']);
        
        // 记录日志
        AdminLogModel::record([
            'admin_id' => $adminInfo['id'] ?? 0,
            'admin_name' => $adminInfo['name'] ?? '-',
            'info' => json_encode($input, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),
            'status' => 1,
        ]);
        
        return $response;
    }

}
