<?php

declare (strict_types = 1);

namespace Larke\Admin\Extension;

use Illuminate\Support\Str;

/*
 * 扩展请求相关
 *
 * @create 2021-3-5
 * @author deatil
 */
class Request
{
    /**
     * 匹配请求路径
     *
     * @param string $path
     * @param null|string $current
     *
     * @return bool
     */
    public static function matchPath(string $path, ?string $current = null): bool
    {
        $request = request();
        $current = $current ?: $request->decodedPath();

        if (Str::contains($path, ':')) {
            [$methods, $path] = explode(':', $path);

            $methods = array_map('strtoupper', explode(',', $methods));

            if (! empty($methods) 
                && ! in_array($request->method(), $methods)
            ) {
                return false;
            }
        }

        // 判断路由名称
        if ($request->routeIs($path)) {
            return true;
        }

        if (! Str::contains($path, '*')) {
            return $path === $current;
        }

        $path = str_replace(['*', '/'], ['([0-9a-z-_,])*', "\/"], $path);

        return preg_match("/$path/i", $current) == 1;
    }

}
