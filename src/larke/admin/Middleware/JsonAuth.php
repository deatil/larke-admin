<?php

namespace Larke\Admin\Middleware;

use Closure;

use Larke\Admin\Traits\Json as JsonTrait;
use Larke\Admin\Service\JwtAuth as JwtAuthService;

/*
 * toke验证
 *
 * @create 2020-10-19
 * @author deatil
 */
class JsonAuth
{
    use JsonTrait;
    
    public function handle($request, Closure $next)
    {
        $this->jwtCheck();

        return $next($request);
    }
    
    /*
     * jwt验证
     */
    protected function jwtCheck()
    {
        $token = request()->header('token');
        if (!$token) {
            $this->errorJson('token不能为空');
        }
        
        if (count(explode('.', $token)) <> 3) {
            $this->errorJson('token格式错误');
        }
        
        $jwtAuth = JwtAuthService::getInstance();
        $jwtAuth->withToken($token)->decode();
        if (!($jwtAuth->validate() && $jwtAuth->verify())) {
            $this->errorJson('token已过期');
        }
        
        $adminid = $jwtAuth->getClaim('adminid');
        
        config([
            'larke.adminid' => $adminid,
        ]);
    }

}
