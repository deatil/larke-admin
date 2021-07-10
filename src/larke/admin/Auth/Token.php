<?php

declare (strict_types = 1);

namespace Larke\Admin\Auth;

use Larke\Admin\Jwt\Jwt;
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
        $expiresIn = config('larkeadmin.passport.refresh_expires_in', 604800);
        
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
            ->getEnToken();
        
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
            ->getEnToken();
        
        return $token;
    }
    
    /**
     * 解码鉴权 token
     */
    public function decodeAccessToken(string $token)
    {
        $this->checkToken($token);
        
        $jwt = app('larke-admin.jwt')
            ->withJti(config('larkeadmin.passport.access_token_id'))
            ->withDeToken($token)
            ->decode();
        
        return $jwt;
    }
    
    /**
     * 解码刷新 token
     */
    public function decodeRefreshToken(string $token)
    {
        $this->checkToken($token);
        
        $jwt = app('larke-admin.jwt')
            ->withJti(config('larkeadmin.passport.refresh_token_id'))
            ->withDeToken($token)
            ->decode();
            
        return $jwt;
    }
    
    /**
     * 验证格式
     */
    public function validate(Jwt $decodeToken) 
    {
        if (! $decodeToken->validate()) {
            throw new JWTException(__('token数据错误'));
        }
    }
    
    /**
     * 验证签名
     */
    public function verify(Jwt $decodeToken) 
    {
        if (! $decodeToken->verify()) {
            throw new JWTException(__('token验证失败'));
        }
    }
    
    /**
     * 检测 token
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
