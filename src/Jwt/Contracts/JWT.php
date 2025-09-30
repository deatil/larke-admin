<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Contracts;

use Larke\JWT\Contracts\Token;
use Larke\JWT\Contracts\UnencryptedToken;

/**
 * JWT
 *
 * @create 2023-3-10
 * @author deatil
 */
interface JWT
{
    /**
     * 设置 header
     */
    public function withHeader(string $name, ?mixed $value = null);
    
    /**
     * 设置 claim
     */
    public function withClaim(string $claim, ?mixed $value = null);
    
    /**
     * 设置 iss
     */
    public function withIss(string $issuer): self;
    
    /**
     * 设置 aud
     */
    public function withAud(string $audience): self;
    
    /**
     * 设置 subject
     */
    public function withSub(string $subject): self;
    
    /**
     * 设置 jti
     */
    public function withJti(string $jti): self;
    
    /**
     * 设置 issuedAt
     */
    public function withIat(int $iat = 0): self;
    
    /**
     * 设置 nbf
     */
    public function withNbf(int $notBeforeTime): self;
    
    /**
     * 设置 expTime
     */
    public function withExp(int $expTime): self;
    
    /**
     * 设置 leeway
     */
    public function withLeeway(int $leeway): self;
    
    /**
     * 签名方法
     */
    public function withSigningMethod(string $signingMethod): self;
    
    /**
     * 秘钥
     */
    public function withSecret(string $secret): self;
    
    /**
     * 私钥
     */
    public function withPrivateKey(string $privateKey): self;
    
    /**
     * 公钥
     */
    public function withPublicKey(string $publicKey): self;
    
    /**
     * 私钥密码
     */
    public function withPrivateKeyPassword(string $privateKeyPassword): self;
    
    /**
     * 生成 token
     */
    public function createToken(): UnencryptedToken;
    
    /**
     * 解析 token
     */
    public function parseToken(string $token): UnencryptedToken;
    
    /**
     * 验证
     */
    public function validate(UnencryptedToken $token): bool;

    /**
     * 检测
     */
    public function verify(UnencryptedToken $token): bool;
    
    /**
     * 获取 Header
     */
    public function getHeader(Token $token, string $name): mixed;
    
    /**
     * 获取 Headers
     */
    public function getHeaders(Token $token): array;

    /**
     * 获取 token 存储数据
     */
    public function getClaim(UnencryptedToken $token, string $name): mixed;
    
    /**
     * 获取 Claims
     */
    public function getClaims(UnencryptedToken $token): array;
}
