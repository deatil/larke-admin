<?php

declare (strict_types = 1);

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

/**
 * 请求options过滤
 *
 * 对于 options 请求，需要在
 * App\Http\Kernel->middleware 属性添加或者配置官方自带的 Cors 中间件：
 * 
 * \Larke\Admin\Middleware\RequestOptions::class,
 *
 * @create 2020-11-8
 * @author deatil
 */
class RequestOptions
{
    use ResponseJsonTrait;
    
    public function handle($request, Closure $next)
    {
        if ($this->isLakeAdminRequest($request) 
            && $request->isMethod('OPTIONS')
        ) {
            $this->success('');
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
