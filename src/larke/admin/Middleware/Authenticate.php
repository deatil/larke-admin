<?php

namespace Larke\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

use Larke\Admin\Traits\Json as JsonTrait;
use Larke\Admin\Model\Admin as AdminModel;

/*
 * jwt 验证
 *
 * @create 2020-10-19
 * @author deatil
 */
class Authenticate
{
    use JsonTrait;
    
    public function handle($request, Closure $next)
    {
        if (!$this->shouldPassThrough($request)) {
            $this->jwtCheck();
        }
        
        return $next($request);
    }
    
    /*
     * jwt验证
     */
    protected function jwtCheck()
    {
        $token = request()->header('token');
        if (!$token) {
            $this->errorJson(__('token不能为空'));
        }
        
        if (count(explode('.', $token)) <> 3) {
            $this->errorJson(__('token格式错误'));
        }
        
        if (Cache::has(md5($token))) {
            $this->errorJson(__('token已失效'));
        }
        
        $jwtAuth = app('larke.jwt');
        
        try {
            $jwtAuth->withToken($token)->decode();
        } catch(\Exception $e) {
            $this->errorJson(__("JWT解析错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if (!($jwtAuth->validate() && $jwtAuth->verify())) {
            $this->errorJson(__('token已过期'));
        }
        
        try {
            $adminid = $jwtAuth->getClaim('adminid');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT解析错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        $adminInfo = AdminModel::where('id', $adminid)->first()->toArray();
        if (empty($adminInfo) || $adminInfo['status'] != 1) {
            $this->errorJson(__('帐号不存在或者已被锁定'));
        }
        
        config([
            'larke.auth' => [
                'adminid' => $adminid,
                'admininfo' => $adminInfo,
            ],
        ]);
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
        $excepts = array_merge(config('larke.auth.excepts', []), [
            'larke-admin-passport-login',
            'larke-admin-passport-refresh-token',
        ]);

        return collect($excepts)
            ->contains(function ($except) {
                $requestUrl = \Route::currentRouteName();
                return ($except == $requestUrl);
            });
    }

}
