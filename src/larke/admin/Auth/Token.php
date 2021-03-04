<?php

declare (strict_types = 1);

namespace Larke\Admin\Auth;

use Larke\Admin\Exception\JWTException;

/*
 * Token
 *
 * @create 2021-3-2
 * @author deatil
 */
class Token
{
    /**
     * 鉴权token过期时间
     */
    public function getAccessTokenExpiresIn()
    {
        $expiresIn = config('larkeadmin.passport.access_expires_in', 86400);
        
        return $expiresIn;
    }
    
    /**
     * 刷新token过期时间
     */
    public function getRefreshTokenExpiresIn()
    {
        $expiresIn = config('larkeadmin.passport.access_expires_in', 86400);
        
        return $expiresIn;
    }
    
    /**
     * 生成鉴权token
     */
    public function buildAccessToken(array $data)
    {
        $expiresIn = $this->getAccessTokenExpiresIn();
        $token = app('larke-admin.jwt')
            ->withData($data)
            ->withExp($expiresIn)
            ->withJti(config('larkeadmin.passport.access_token_id'))
            ->encode()
            ->getToken();
        
        return $token;
    }
    
    /**
     * 生成刷新token
     */
    public function buildRefreshToken(array $data)
    {
        $expiresIn = $this->getRefreshTokenExpiresIn();
        $token = app('larke-admin.jwt')
            ->withData($data)
            ->withExp($expiresIn)
            ->withJti(config('larkeadmin.passport.refresh_token_id'))
            ->encode()
            ->getToken();
        
        return $token;
    }
    
    /**
     * 解码鉴权token
     */
    public function decodeAccessToken(string $token)
    {
        $this->checkToken($token);
        
        $data = app('larke-admin.jwt')
            ->withJti(config('larkeadmin.passport.access_token_id'))
            ->withToken($token)
            ->decode();
        
        return $data;
    }
    
    /**
     * 解码刷新token
     */
    public function decodeRefreshToken(string $token)
    {
        $this->checkToken($token);
        
        $data = app('larke-admin.jwt')
            ->withJti(config('larkeadmin.passport.refresh_token_id'))
            ->withToken($token)
            ->decode();
            
        return $data;
    }
    
    /**
     * 检测token
     */
    public function checkToken(string $token) 
    {
        if (empty($token)) {
            throw new JWTException(__('token格式错误'));
        }
        
        if (count(explode('.', $token)) <> 3) {
            throw new JWTException(__('token格式错误'));
        }
    }
    
}
