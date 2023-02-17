<?php

declare (strict_types = 1);

namespace Larke\Admin\Auth;

use Larke\Admin\Jwt\JwtManager;
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
     * 鉴权 token 过期时间
     */
    public function getAccessTokenExpiresIn(): int
    {
        $expiresIn = config('larkeadmin.passport.access_expires_in', 86400);
        
        return (int) $expiresIn;
    }
    
    /**
     * 刷新 token 过期时间
     */
    public function getRefreshTokenExpiresIn(): int
    {
        $expiresIn = config('larkeadmin.passport.refresh_expires_in', 604800);
        
        return (int) $expiresIn;
    }
    
    /**
     * 生成鉴权 token
     */
    public function buildAccessToken(array $data): string
    {
        $expiresIn = $this->getAccessTokenExpiresIn();
        $jti = config('larkeadmin.passport.access_token_id');
        
        $token = app('larke-admin.jwt')
            ->withData($data)
            ->withExp($expiresIn)
            ->withJti($jti)
            ->encode()
            ->getEnToken();
        
        return $token;
    }
    
    /**
     * 生成刷新 token
     */
    public function buildRefreshToken(array $data): string
    {
        $expiresIn = $this->getRefreshTokenExpiresIn();
        $jti = config('larkeadmin.passport.refresh_token_id');
        
        $token = app('larke-admin.jwt')
            ->withData($data)
            ->withExp($expiresIn)
            ->withJti($jti)
            ->encode()
            ->getEnToken();
        
        return $token;
    }
    
    /**
     * 解码鉴权 token
     */
    public function decodeAccessToken(string $token): JwtManager
    {
        $this->checkToken($token);
        
        $jti = config('larkeadmin.passport.access_token_id');
        
        $jwt = app('larke-admin.jwt')
            ->withJti($jti)
            ->withDeToken($token)
            ->decode();
        
        return $jwt;
    }
    
    /**
     * 解码刷新 token
     */
    public function decodeRefreshToken(string $token): JwtManager
    {
        $this->checkToken($token);
        
        $jti = config('larkeadmin.passport.refresh_token_id');
        
        $jwt = app('larke-admin.jwt')
            ->withJti($jti)
            ->withDeToken($token)
            ->decode();
            
        return $jwt;
    }
    
    /**
     * 验证格式
     *
     * @param \Larke\Admin\Jwt\JwtManager $decodeToken
     */
    public function validate(JwtManager $decodeToken): void
    {
        if (! $decodeToken->validate()) {
            throw new JWTException(__('token数据错误'));
        }
    }
    
    /**
     * 验证签名
     *
     * @param \Larke\Admin\Jwt\JwtManager $decodeToken
     */
    public function verify(JwtManager $decodeToken): void
    {
        if (! $decodeToken->verify()) {
            throw new JWTException(__('token验证失败'));
        }
    }
    
    /**
     * 检测 token
     */
    public function checkToken(string $token): void
    {
        if (empty($token)) {
            throw new JWTException(__('token格式错误'));
        }
        
        if (count(explode('.', $token)) <> 3) {
            throw new JWTException(__('token格式错误'));
        }
    }
    
}
