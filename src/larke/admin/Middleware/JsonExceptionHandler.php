<?php

declare (strict_types = 1);

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Exception\JsonHandler;

/*
 * 请求options过滤
 *
 * @create 2021-1-13
 * @author deatil
 */
class JsonExceptionHandler
{
    public function handle($request, Closure $next)
    {
        if ($this->isLakeAdminRequest($request)) {
            app()->singleton(
                \Illuminate\Contracts\Debug\ExceptionHandler::class,
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
    protected function isLakeAdminRequest($request)
    {
        $path = trim(config('larkeadmin.route.prefix'), '/') ?: '/';

        return $request->is($path) ||
               $request->is(trim($path.'/*', '/'));
    }

}
