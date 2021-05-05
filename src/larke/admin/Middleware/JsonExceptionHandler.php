<?php

declare (strict_types = 1);

namespace Larke\Admin\Middleware;

use Closure;

use Illuminate\Contracts\Debug\ExceptionHandler;

use Larke\Admin\Exception\JsonHandler;

/**
 * 绑定自定义错误处理
 *
 * @create 2021-1-13
 * @author deatil
 */
class JsonExceptionHandler
{
    public function handle($request, Closure $next)
    {
        if ($this->isLarkeAdminRequest($request)) {
            app()->singleton(
                ExceptionHandler::class,
                JsonHandler::class
            );
        }
        
        return $next($request);
    }

    /**
     * 系统请求检测
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isLarkeAdminRequest($request)
    {
        $path = trim(config('larkeadmin.route.prefix'), '/') ?: '/';

        return $request->is($path) ||
               $request->is(trim($path.'/*', '/'));
    }

}
