<?php

declare (strict_types = 1);

namespace Larke\Admin\Middleware;

use Closure;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

/*
 * 检测多语言
 *
 * @create 2021-2-7
 * @author deatil
 */
class CheckLang
{
    public function handle($request, Closure $next)
    {
        // 切换语言
        $locale = request()->header('Locale-language');
        
        // 检测语言格式
        $validator = Validator::make([
            'locale' => $locale,
        ], [
            'locale' => 'required|alpha_dash',
        ], [
            'locale.required' => __('请选择要查询的语言'),
            'locale.alpha_dash' => __('语言名称格式错误'),
        ]);
        
        if (! $validator->fails() && ! App::isLocale($locale)) {
            // 设置语言
            App::setLocale($locale);
        }
        
        return $next($request);
    }

}
