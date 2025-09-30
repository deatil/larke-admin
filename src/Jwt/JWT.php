<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt;

use Exception;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use Larke\JWT\Contracts\UnencryptedToken;

use Larke\Admin\Exception\JWTException;
use Larke\Admin\Contracts\Crypt as CryptContract;
use Larke\Admin\Jwt\Contracts\JWT as JWTContract;

/**
 * jwt 管理器
 *
 * @create 2022-3-13
 * @author deatil
 */
class JWT
{
    /**
     * jwt
     */
    private JWTContract $jwt;
    
    /**
     * 加密
     */
    private CryptContract $crypt;
    
    /**
     * 配置
     *
     * @var Collection
     */
    private Collection $config;
    
    /**
     * headers
     */
    private array $headers = [];
    
    /**
     * 载荷
     */
    private array $claims = [];
    
    /**
     * 解析后的 token 句柄
     */
    private UnencryptedToken $parsedToken;
    
    /**
     * 构造函数
     */
    public function __construct(
        JWTContract   $jwt, 
        CryptContract $crypt,
        Collection    $config
    ) {
        $this->jwt    = $jwt;
        $this->crypt  = $crypt;
        $this->config = $config;
    }
    
    /**
     * 设置 jwt
     */
    public function withJwt(JWTContract $jwt): self
    {
        $this->jwt = $jwt;
        return $this;
    }
    
    /**
     * 获取 jwt
     */
    public function getJwt(): JWTContract
    {
        return $this->jwt;
    }
    
    /**
     * 设置加密
     */
    public function withCrypt(CryptContract $crypt): self
    {
        $this->crypt = $crypt;
        return $this;
    }
    
    /**
     * 获取加密
     */
    public function getCrypt(): CryptContract
    {
        return $this->crypt;
    }
    
    /**
     * 设置配置
     */
    public function withConfig(Collection $config): self
    {
        $this->config = $config;
        return $this;
    }
    
    /**
     * 设置配置
     */
    public function setConfig(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }
    
    /**
     * 获取配置
     */
    public function getConfig(): Collection
    {
        return $this->config;
    }
    
    /**
     * 设置 header
     */
    public function withHeader(string $name, mixed $value): self
    {
        $this->headers[$name] = $value;
        
        return $this;
    }
    
    /**
     * 批量设置 header
     */
    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->withHeader((string) $name, $value);
        }
        
        return $this;
    }
    
    /**
     * 设置 claim
     */
    public function withClaim(string $claim, mixed $value): self
    {
        $this->claims[$claim] = $value;
        
        return $this;
    }
    
    /**
     * 批量设置 claim
     */
    public function withClaims(array $claims): self
    {
        foreach ($claims as $claim => $value) {
            $this->withClaim((string) $claim, $value);
        }
        
        return $this;
    }
    
    /**
     * 设置 iss
     */
    public function withIss(string $issuer): self
    {
        $this->config['iss'] = $issuer;
        return $this;
    }
    
    /**
     * 设置 aud
     */
    public function withAud(string $audience): self
    {
        $this->config['aud'] = $audience;
        return $this;
    }
    
    /**
     * 设置 subject
     */
    public function withSub(string $subject): self
    {
        $this->config['sub'] = $subject;
        return $this;
    }
    
    /**
     * 设置 jti
     */
    public function withJti(string $jti): self
    {
        $this->config['jti'] = $jti;
        return $this;
    }
    
    /**
     * 设置 issuedAt
     */
    public function withIat(int $issuedAt): self
    {
        $this->config['iat'] = $issuedAt;
        return $this;
    }
    
    /**
     * 设置 nbf
     */
    public function withNbf(int $notBeforeTime): self
    {
        if ($notBeforeTime < 0) {
            $notBeforeTime = 0;
        }
        
        $this->config['nbf'] = $notBeforeTime;
        return $this;
    }
    
    /**
     * 设置 expTime
     */
    public function withExp(int $expTime): self
    {
        $this->config['exp'] = $expTime;
        return $this;
    }
    
    /**
     * 设置 JWT
     */
    private function loadJwt(): self
    {
        // 发布者
        $this->jwt->withIss($this->configGet('iss', '')); 
        // 接收者
        $this->jwt->withAud($this->configGet('aud', '')); 
        // 主题
        $this->jwt->withSub($this->configGet('sub', '')); 
        // 对当前token设置的标识
        $this->jwt->withJti($this->configGet('jti', '')); 
        // token创建时间
        $this->jwt->withIat($this->configGet('iat', 0)); 
        // 多少秒内无法使用
        $this->jwt->withNbf($this->configGet('nbf', 0)); 
        // 过期时间
        $this->jwt->withExp($this->configGet('exp', 0)); 
        // leeway
        $this->jwt->withLeeway($this->configGet('leeway', 0)); 

        // 加密方式
        $algorithm = $this->configGet('signer.algorithm', 'HS256');
        $this->jwt->withSigningMethod($algorithm);
        
        // 密码
        $secrect = $this->configGet('signer.secrect', '');
        $this->jwt->withSecret($secrect);
        
        // 私钥
        $privateKey = $this->configGet('signer.private_key', '');
        $this->jwt->withPrivateKey($privateKey);
        
        // 私钥密码
        $passphrase = $this->configGet('signer.passphrase', null);
        $this->jwt->withPrivateKeyPassword($passphrase);

        // 公钥
        $publicKey = $this->configGet('signer.public_key', '');
        $this->jwt->withPublicKey($publicKey);
        
        return $this;
    }
    
    /**
     * 编码 jwt token
     */
    public function buildToken(): string
    {
        $this->loadJwt();
        
        foreach ($this->headers as $headerKey => $header) {
            $this->jwt->withHeader($headerKey, $header);
        }
        
        foreach ($this->claims as $claimKey => $claim) {
            $this->jwt->withClaim($claimKey, $claim);
        }
        
        try {
            $token = $this->jwt->createToken()->toString();
        } catch(Exception $e) {
            Log::error('larke-admin-jwt-createToken: '.$e->getMessage());
            
            throw new JWTException(__('larke-admin::jwt.encode_fail'));
        }
        
        return $token;
    }
    
    /**
     * 解码
     */
    public function parseToken(string $token): self
    {
        try {
            $this->parsedToken = $this->jwt->parseToken($token); 
        } catch(Exception $e) {
            Log::error('larke-admin-jwt-parsedToken: '.$e->getMessage());
            
            throw new JWTException(__('larke-admin::jwt.decode_fail'));
        }
        
        return $this;
    }
    
    /**
     * 验证
     */
    public function validate(): bool
    {
        $this->loadJwt();
        
        return $this->jwt->validate($this->parsedToken);
    }

    /**
     * 检测
     */
    public function verify(): bool
    {
        $this->loadJwt();
    
        return $this->jwt->verify($this->parsedToken);
    }

    /**
     * 获取 parsedToken
     */
    public function getParsedToken(): UnencryptedToken
    {
        return $this->parsedToken;
    }
    
    /**
     * 获取 Header
     */
    public function getHeader(string $name): mixed
    {
        return $this->jwt->getHeader($this->parsedToken, $name);
    }
    
    /**
     * 获取 Headers
     */
    public function getHeaders(): array
    {
        return $this->jwt->getHeaders($this->parsedToken);
    }

    /**
     * 获取 token 存储数据
     */
    public function getClaim(string $name): mixed
    {
        return $this->jwt->getClaim($this->parsedToken, $name);
    }
    
    /**
     * 获取 Claims
     */
    public function getClaims(): array
    {
        return $this->jwt->getClaims($this->parsedToken);
    }
    
    /**
     * 加密载荷数据
     */
    public function withData(string $claim, mixed $value): self
    {
        if (!empty($claim) && !empty($value)) {
            $value = $this->crypt->encrypt(
                $value, 
                $this->base64Decode(
                    $this->configGet('passphrase', '')
                )
            );
            
            $this->withClaim($claim, $value);
        }
        
        return $this;
    }
    
    /**
     * 加密载荷数据
     */
    public function withDatas(array $claims): self
    {
        foreach ($claims as $claim => $value) {
            $this->withData((string) $claim, $value);
        }
        
        return $this;
    }

    /**
     * 载荷解密后数据
     */
    public function getData(string $name): mixed
    {
        $claim = $this->crypt->decrypt(
            $this->getClaim($name), 
            $this->base64Decode(
                $this->configGet('passphrase', '')
            )
        );
        
        return $claim;
    }
    
    /**
     * base64 解密
     */
    public function base64Decode(string $contents): string
    {
        return base64_decode($contents, true);
    }
    
    /**
     * 获取配置
     */
    protected function configGet(string $key, ?mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

}
