<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt;

use DateTimeImmutable;

// 文件夹引用
use Larke\JWT\Builder;
use Larke\JWT\Parser;
use Larke\JWT\ValidationData;
use Larke\JWT\Clock\SystemClock;

use Larke\Admin\Exception\JWTException;

/**
 * jwt
 *
 * @create 2020-10-19
 * @author deatil
 */
class Jwt
{
    /**
     * headers
     */
    private $headers = [];
    
    /**
     * 载荷
     */
    private $claims = [];
    
    /**
     * 载荷 issuer
     */
    private $issuer = '';
    
    /**
     * 载荷 audience
     */
    private $audience = '';
    
    /**
     * 载荷 subject
     */
    private $subject = '';
    
    /**
     * jwt 签发时间
     */
    private $issuedAt = 0;
    
    /**
     * jwt 过期时间
     */
    private $expTime = 3600;
    
    /**
     * 时间内不能访问
     */
    private $notBeforeTime = 0;
    
    /**
     * 时间差兼容
     */
    private $leeway = 0;
    
    /**
     * 签名方法
     */
    private $signingMethod = '';
    
    /**
     * 秘钥
     */
    private $secret = '';
    
    /**
     * 私钥
     */
    private $privateKey = '';
    
    /**
     * 公钥
     */
    private $publicKey = '';
    
    /**
     * 私钥密码
     */
    private $privateKeyPassword = '';
    
    /**
     * 当前时间
     */
    private $now;
    
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
    public function withHeader($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->withHeader($k, $v);
            }
            
            return $this;
        }
        
        $this->headers[(string) $name] = $value;
        return $this;
    }
    
    /**
     * 设置 claim
     */
    public function withClaim($claim, $value = null)
    {
        if (is_array($claim)) {
            foreach ($claim as $k => $v) {
                $this->withClaim($k, $v);
            }
            
            return $this;
        }
        
        $this->claims[(string) $claim] = $value;
        return $this;
    }
    
    /**
     * 设置 iss
     */
    public function withIss($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }
    
    /**
     * 设置 aud
     */
    public function withAud($audience)
    {
        $this->audience = $audience;
        return $this;
    }
    
    /**
     * 设置 subject
     */
    public function withSub($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * 设置 jti
     */
    public function withJti($jti)
    {
        $this->jti = $jti;
        return $this;
    }
    
    /**
     * 设置 issuedAt
     */
    public function withIat($issuedAt = null)
    {
        if (empty($issuedAt)) {
            $issuedAt = $this->now;
        }
        
        $this->issuedAt = $issuedAt;
        return $this;
    }
    
    /**
     * 设置 nbf
     */
    public function withNbf($notBeforeTime)
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
    public function withExp($expTime)
    {
        $this->expTime = $this->now->modify("+{$expTime} hour");
        return $this;
    }
    
    /**
     * 设置 leeway
     */
    public function withLeeway($leeway)
    {
        $this->leeway = $leeway;
        return $this;
    }
    
    /**
     * 签名方法
     */
    public function withSigningMethod($signingMethod)
    {
        $this->signingMethod = $signingMethod;
        return $this;
    }
    
    /**
     * 秘钥
     */
    public function withSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }
    
    /**
     * 私钥
     */
    public function withPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
        return $this;
    }
    
    /**
     * 公钥
     */
    public function withPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }
    
    /**
     * 私钥密码
     */
    public function withPrivateKeyPassword($privateKeyPassword)
    {
        $this->privateKeyPassword = $privateKeyPassword;
        return $this;
    }
    
    /**
     * 获取签名
     */
    public function getSigner()
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
    public function makeToken()
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
    public function parseToken($token)
    {
        $token = (new Parser())->parse((string) $token); 
        
        return $token;
    }
    
    /**
     * 验证
     */
    public function validate($token)
    {
        $data = new ValidationData($this->now, $this->leeway); 
        $data->issuedBy($this->issuer);
        $data->permittedFor($this->audience);
        $data->identifiedBy($this->jti);
        $data->relatedTo($this->subject);
        
        return $token->validate($data);
    }

    /**
     * 检测
     */
    public function verify($token)
    {
        $sign = $this->getSigner();
        
        return $token->verify($sign->getSigner(), $sign->getVerifySecrect());
    }
    
    /**
     * 获取 Header
     */
    public function getHeader($token, $name)
    {
        return $token->getHeader($name);
    }
    
    /**
     * 获取 Headers
     */
    public function getHeaders($token)
    {
        return $token->getHeaders()->all();
    }

    /**
     * 获取 token 存储数据
     */
    public function getClaim($token, $name)
    {
        return $token->getClaim($name);
    }
    
    /**
     * 获取 Claims
     */
    public function getClaims($token)
    {
        $claims = $token->getClaims()->all();
        
        $data = [];
        foreach ($claims as $claim) {
            $data[$claim->getName()] = $claim->getValue();
        }
        
        return $data;
    }
}
