<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Driver;

use DateTimeImmutable;

use Larke\JWT\Parser;
use Larke\JWT\Builder;
use Larke\JWT\Validator;
use Larke\JWT\ValidationData;
use Larke\JWT\Clock\SystemClock;
use Larke\JWT\Contracts\Token;
use Larke\JWT\Contracts\UnencryptedToken;

use Larke\Admin\Jwt\Signer;
use Larke\Admin\Jwt\Contracts\JWT as JWTContract;
use Larke\Admin\Jwt\Contracts\Signer as SignerContract;
use Larke\Admin\Exception\JWTException;

/**
 * jwt
 *
 * @create 2020-10-19
 * @author deatil
 */
class LarkeJWT implements JWTContract
{
    /**
     * headers
     */
    private array $headers = [];
    
    /**
     * 载荷
     */
    private array $claims = [];
    
    /**
     * 载荷 issuer
     */
    private string $issuer = '';
    
    /**
     * 载荷 audience
     */
    private string $audience = '';
    
    /**
     * 载荷 subject
     */
    private string $subject = '';
    
    /**
     * jwt 签发时间
     */
    private DateTimeImmutable $issuedAt;
    
    /**
     * jwt 过期时间
     */
    private DateTimeImmutable $expTime;
    
    /**
     * 时间内不能访问
     */
    private DateTimeImmutable $notBeforeTime;
    
    /**
     * 时间差兼容
     */
    private int $leeway = 0;
    
    /**
     * 签名方法
     */
    private string $signingMethod = '';
    
    /**
     * 秘钥
     */
    private string $secret = '';
    
    /**
     * 私钥
     */
    private string $privateKey = '';
    
    /**
     * 公钥
     */
    private string $publicKey = '';
    
    /**
     * 私钥密码
     */
    private string $privateKeyPassword = '';
    
    /**
     * 当前时间
     */
    private DateTimeImmutable $now;
    
    /**
     * 构造函数
     */
    public function __construct(DateTimeImmutable $now = null) 
    {
        $this->now = $now ?: SystemClock::fromSystemTimezone()->now();
    }
    
    /**
     * 设置 header
     */
    public function withHeader(string $name, mixed $value = null)
    {
        $this->headers[$name] = $value;
        
        return $this;
    }
    
    /**
     * 设置 claim
     */
    public function withClaim(string $claim, mixed $value = null)
    {
        $this->claims[$claim] = $value;
        
        return $this;
    }
    
    /**
     * 设置 iss
     */
    public function withIss(string $issuer): self
    {
        $this->issuer = $issuer;
        return $this;
    }
    
    /**
     * 设置 aud
     */
    public function withAud(string $audience): self
    {
        $this->audience = $audience;
        return $this;
    }
    
    /**
     * 设置 subject
     */
    public function withSub(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * 设置 jti
     */
    public function withJti(string $jti): self
    {
        $this->jti = $jti;
        return $this;
    }
    
    /**
     * 设置 issuedAt
     */
    public function withIat(int $iat = 0): self
    {
        if ($iat == 0) {
            $issuedAt = $this->now;
        } else {
            $issuedAt = $this->now->setTimestamp($iat);
        }
        
        $this->issuedAt = $issuedAt;
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
        
        $this->notBeforeTime = $this->now->modify("+{$notBeforeTime} minute");
        return $this;
    }
    
    /**
     * 设置 expTime
     */
    public function withExp(int $expTime): self
    {
        $this->expTime = $this->now->modify("+{$expTime} hour");
        return $this;
    }
    
    /**
     * 设置 leeway
     */
    public function withLeeway(int $leeway): self
    {
        $this->leeway = $leeway;
        return $this;
    }
    
    /**
     * 签名方法
     */
    public function withSigningMethod(string $signingMethod): self
    {
        $this->signingMethod = $signingMethod;
        return $this;
    }
    
    /**
     * 秘钥
     */
    public function withSecret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }
    
    /**
     * 私钥
     */
    public function withPrivateKey(string $privateKey): self
    {
        $this->privateKey = $privateKey;
        return $this;
    }
    
    /**
     * 公钥
     */
    public function withPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;
        return $this;
    }
    
    /**
     * 私钥密码
     */
    public function withPrivateKeyPassword(string $privateKeyPassword): self
    {
        $this->privateKeyPassword = $privateKeyPassword;
        return $this;
    }
    
    /**
     * 获取签名
     */
    private function getSigner(): SignerContract
    {
        // 加密方式
        $algorithm = Signer::getSigningMethod($this->signingMethod);
        if (empty($algorithm)) {
            throw new JWTException(__('签名类型不存在'));
        }
        
        // 加密方式
        $config = collect([
            'secrect'     => $this->secret,
            'private_key' => $this->privateKey,
            'public_key'  => $this->publicKey,
            'passphrase'  => $this->privateKeyPassword,
        ]);
        $signer = new $algorithm($config);
        
        return $signer;
    }
    
    /**
     * 生成 token
     */
    public function createToken(): UnencryptedToken
    {
        $builder = new Builder();
        
        // 发布者
        $builder->issuedBy($this->issuer); 
        // 接收者
        $builder->permittedFor($this->audience); 
        // 主题
        $builder->relatedTo($this->subject); 
        // 对当前token设置的标识
        $builder->identifiedBy($this->jti); 
        // token创建时间
        $builder->issuedAt($this->issuedAt); 
        // 多少秒内无法使用
        $builder->canOnlyBeUsedAfter($this->notBeforeTime); 
        // 过期时间
        $builder->expiresAt($this->expTime); 
        
        // 添加 header 信息
        foreach ($this->headers as $headerKey => $header) {
            $builder->withHeader($headerKey, $header);
        }
        
        // 添加载荷信息
        foreach ($this->claims as $claimKey => $claim) {
            $builder->withClaim($claimKey, $claim);
        }
        
        $sign  = $this->getSigner();
        $token = $builder->getToken($sign->getSigner(), $sign->getSignSecrect());
        
        return $token;
    }
    
    /**
     * 解析 token
     */
    public function parseToken(string $token): UnencryptedToken
    {
        $token = (new Parser())->parse($token); 
        
        return $token;
    }
    
    /**
     * 验证
     */
    public function validate(UnencryptedToken $token): bool
    {
        $data = new ValidationData($this->now, $this->leeway); 
        $data->issuedBy($this->issuer);
        $data->permittedFor($this->audience);
        $data->identifiedBy($this->jti);
        $data->relatedTo($this->subject);
        
        $validation = new Validator();
        
        return $validation->validate($token, $data);
    }

    /**
     * 检测
     */
    public function verify(UnencryptedToken $token): bool
    {
        $sign = $this->getSigner();
        
        $validation = new Validator();
        
        return $validation->verify($token, $sign->getSigner(), $sign->getVerifySecrect());
    }
    
    /**
     * 获取 Header
     */
    public function getHeader(Token $token, string $name): mixed
    {
        return $token->headers()->get($name);
    }
    
    /**
     * 获取 Headers
     */
    public function getHeaders(Token $token): array
    {
        return $token->headers()->all();
    }

    /**
     * 获取 token 存储数据
     */
    public function getClaim(UnencryptedToken $token, string $name): mixed
    {
        return $token->claims()->get($name);
    }
    
    /**
     * 获取 Claims
     */
    public function getClaims(UnencryptedToken $token): array
    {
        return $token->claims()->all();
    }
}
