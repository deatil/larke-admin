<?php

namespace Larke\Admin\Jwt;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\ValidationData;

use Larke\Admin\Contracts\Jwt as JwtContract;

/**
 * jwt
 *
 * @create 2020-10-19
 * @author deatil
 */
class Jwt implements JwtContract
{
    /**
     * alg
     */
    private $alg = 'HS256';
    
    /**
     * claim issuer
     */
    private $issuer = '';
    
    /**
     * claim audience
     */
    private $audience = '';
    
    /**
     * subject
     */
    private $subject = '';
    
    /**
     * jwt 过期时间
     */
    private $expTime = 3600;
    
    /**
     * 时间内不能访问
     */
    private $notBeforeTime = 10;
    
    /**
     * decode token
     */
    private $decodeToken;
    
    /**
     * jwt token
     */
    private $token;
    
    /**
     * jwt claims
     */
    private $claims = [];
    
    /**
     * 密钥
     */
    private $secrect = '';
    
    /**
     * 私钥地址
     */
    private $privateKey = '';
    
    /**
     * 公钥地址
     */
    private $publicKey = '';
    
    /**
     * 填写 RSA(RSA and ECDSA) 或者 SECRECT
     */
    private $signerType = 'SECRECT';
    
    /**
     * 设置alg
     */
    public function withAlg($alg)
    {
        $this->alg = $alg;
        return $this;
    }
    
    /**
     * 设置iss
     */
    public function withIss($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }
    
    /**
     * 设置aud
     */
    public function withAud($audience)
    {
        $this->audience = $audience;
        return $this;
    }
    
    /**
     * 设置subject
     */
    public function withSub($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * 设置jti
     */
    public function withJti($jti)
    {
        $this->jti = $jti;
        return $this;
    }
    
    /**
     * 设置notBeforeTime
     */
    public function withNotBeforeTime($notBeforeTime)
    {
        if ($notBeforeTime < 0) {
            $notBeforeTime = 0;
        }
        
        $this->notBeforeTime = $notBeforeTime;
        return $this;
    }
    
    /**
     * 设置expTime
     */
    public function withExpTime($expTime)
    {
        $this->expTime = $expTime;
        return $this;
    }
    
    /**
     * 设置token
     */
    public function withToken($token)
    {
        $this->token = $token;
        return $this;
    }
    
    /**
     * 获取token
     */
    public function getToken()
    {
        return (string) $this->token;
    }
    
    /**
     * 设置claim
     */
    public function withClaim($claim)
    {
        $this->claims = array_merge($this->claims, $claim);
        return $this;
    }
    
    /**
     * 设置claims
     */
    public function withClaims($claims)
    {
        $this->claims = $claims;
        return $this;
    }
    
    /**
     * 设置secrect
     */
    public function withSecrect($secrect)
    {
        $this->secrect = $secrect;
        return $this;
    }
    
    /**
     * 设置 privateKey
     */
    public function withPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
        return $this;
    }
    
    /**
     * 设置 publicKey
     */
    public function withPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }
    
    /**
     * 设置 signerType
     */
    public function withSignerType($signerType)
    {
        if ($signerType != 'RSA') {
            $signerType = 'SECRECT';
        }
        $this->signerType = $signerType;
        return $this;
    }
    
    /**
     * 编码 jwt token
     */
    public function encode()
    {
        $Builder = new Builder();
        
        $Builder->withHeader('alg', $this->alg);
        $Builder->issuedBy($this->issuer); // 发布者
        $Builder->permittedFor($this->audience); // 接收者
        
        if (!empty($this->subject)) {
            $Builder->relatedTo($this->subject); // 主题
        }
        
        $Builder->identifiedBy($this->jti); // 对当前token设置的标识
        
        if (!empty($this->claims)) {
            foreach ($this->claims as $claimKey => $claim) {
                $Builder->withClaim($claimKey, $claim);
            }
        }
        
        $time = time();
        $Builder->issuedAt($time); // token创建时间
        $Builder->canOnlyBeUsedAfter($time + 10); // 多少秒内无法使用
        $Builder->expiresAt($time + $this->expTime); // 过期时间
        
        if ($this->signerType == 'RSA') {
            $key = 'file://'.$this->privateKey;
        } else {
            $key = $this->secrect;
        }
        
        $signer = new Sha256();
        $secrect = new Key($key);
        $this->token = $Builder->getToken($signer, $secrect);
        
        return $this;
    }
    
    /**
     * 解码
     */
    public function decode()
    {
        if (!$this->decodeToken) {
            $Parser = (new Parser());
            
            try {
                $this->decodeToken = $Parser->parse((string) $this->token); 
            } catch(\Exception $e) {
                $this->decodeToken = false;
            }
        }
        
        return $this;
    }
    
    /**
     * validate
     */
    public function validate()
    {
        if (!$this->decodeToken) {
            return false;
        }
        
        $data = new ValidationData(); 
        $data->setIssuer($this->issuer);
        $data->setAudience($this->audience);
        $data->setId($this->jti);
        $data->setSubject($this->subject);
        $data->setCurrentTime(time());

        return $this->decodeToken->validate($data);
    }

    /**
     * verify token
     */
    public function verify()
    {
        if (!$this->decodeToken) {
            return false;
        }
        
        if ($this->signerType == 'RSA') {
            $key = 'file://'.$this->publicKey;
        } else {
            $key = $this->secrect;
        }
        
        $signer = new Sha256();
        $secrect = new Key($key);
        return $this->decodeToken->verify($signer, $secrect);
    }

    /**
     * 获取token存储数据
     */
    public function getClaim($name = 'uid')
    {
        if (!$this->decodeToken) {
            return false;
        }
        
        try {
            $claim = $this->decodeToken->getClaim($name);
        } catch(\Exception $e) {
            return false;
        }
        
        return $claim;
    }

}
