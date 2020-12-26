<?php

namespace Larke\Admin\Jwt;

use Illuminate\Support\Arr;

use Larke\JWT\Builder;
use Larke\JWT\Parser;
use Larke\JWT\Signer\Key\InMemory;
use Larke\JWT\Signer\Key\LocalFileReference;
use Larke\JWT\ValidationData;

use Larke\Admin\Support\Crypt;
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
     * headers
     */
    private $headers = [];
    
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
    private $notBeforeTime = 0;
    
    /**
     * 时间差兼容
     */
    private $leeway = 0;
    
    /**
     * 载荷加密秘钥
     */
    private $passphrase = '';
    
    /**
     * decode token
     */
    private $decodeToken;
    
    /**
     * jwt token
     */
    private $token = '';
    
    /**
     * jwt claims
     */
    private $claims = [];
    
    /**
     * 配置
     */
    private $signerConfig = [];
    
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
     * 设置 expTime
     */
    public function withExp($expTime)
    {
        $this->expTime = $expTime;
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
        
        $this->notBeforeTime = $notBeforeTime;
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
     * 载荷加密秘钥
     */
    public function withPassphrase($passphrase)
    {
        $this->passphrase = $passphrase;
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
     * 设置配置
     */
    public function withSignerConfig($config)
    {
        $this->signerConfig = array_merge($this->signerConfig, $config);
        return $this;
    }
    
    /**
     * 获取签名
     */
    public function getSigner($isPrivate = true)
    {
        $config = $this->signerConfig;
        
        $algorithm = Arr::get($config, 'algorithm', []);
        if (empty($algorithm)) {
            return false;
        }
        
        $type = Arr::get($algorithm, 'type', '');
        $sha = Arr::get($algorithm, 'sha', '');
        if (empty($type) || empty($sha)) {
            return false;
        }
        
        $signer = '';
        $secrect = '';
        $signerNamespace = '\\Larke\\JWT\\Signer';
        switch ($type) {
            case 'hmac':
                $class = $signerNamespace . '\\Hmac\\' . $sha;
                $signer = new $class;
                $key = Arr::get($config, 'hmac.secrect', '');
                $secrect = InMemory::plainText($key);
                break;
            case 'rsa':
                $class = $signerNamespace . '\\Rsa\\' . $sha;
                $signer = new $class;
                if ($isPrivate) {
                    $privateKey = Arr::get($config, 'rsa.private_key', '');
                    
                    $passphrase = Arr::get($config, 'rsa.passphrase', null);
                    if (!empty($passphrase)) {
                        $passphrase = InMemory::base64Encoded($passphrase)->getContent();
                    }
                    
                    $secrect = LocalFileReference::file($privateKey, $passphrase);
                } else {
                    $publicKey = Arr::get($config, 'rsa.public_key', '');
                    $secrect = LocalFileReference::file($publicKey);
                }
                break;
            case 'ecdsa':
                $class = $signerNamespace . '\\Ecdsa\\' . $sha;
                $signer = new $class;
                if ($isPrivate) {
                    $privateKey = Arr::get($config, 'ecdsa.private_key', '');
                    
                    $passphrase = Arr::get($config, 'ecdsa.passphrase', null);
                    if (!empty($passphrase)) {
                        $passphrase = InMemory::base64Encoded($passphrase)->getContent();
                    }
                    
                    $secrect = LocalFileReference::file($privateKey, $passphrase);
                } else {
                    $publicKey = Arr::get($config, 'ecdsa.public_key', '');
                    $secrect = LocalFileReference::file($publicKey);
                }
                break;
            case 'eddsa':
                $class = $signerNamespace . '\\Eddsa';
                $signer = new $class;
                if ($isPrivate) {
                    $privateKey = Arr::get($config, 'eddsa.private_key', '');
                    $secrect = InMemory::file($privateKey);
                } else {
                    $publicKey = Arr::get($config, 'eddsa.public_key', '');
                    $secrect = InMemory::file($publicKey);
                }
                break;
        }
        
        return [$signer, $secrect];
    }
    
    /**
     * 编码 jwt token
     */
    public function encode()
    {
        $Builder = new Builder();
        
        $Builder->issuedBy($this->issuer); // 发布者
        $Builder->permittedFor($this->audience); // 接收者
        $Builder->relatedTo($this->subject); // 主题
        $Builder->identifiedBy($this->jti); // 对当前token设置的标识
        
        $time = time();
        $Builder->issuedAt($time); // token创建时间
        $Builder->canOnlyBeUsedAfter($time + $this->notBeforeTime); // 多少秒内无法使用
        $Builder->expiresAt($time + $this->expTime); // 过期时间
        
        foreach ($this->headers as $headerKey => $header) {
            $Builder->withHeader($headerKey, $header);
        }
        
        foreach ($this->claims as $claimKey => $claim) {
            $Builder->withClaim($claimKey, $claim);
        }
        
        try {
            list($signer, $secrect) = $this->getSigner(true);
            
            $this->token = $Builder->getToken($signer, $secrect);
        } catch(\Exception $e) {
            $this->token = false;
        }
        
        return $this;
    }
    
    /**
     * 解码
     */
    public function decode()
    {
        if (! $this->decodeToken) {
            try {
                $this->decodeToken = (new Parser())->parse((string) $this->token); 
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
        if (! $this->decodeToken) {
            return false;
        }
        
        $data = new ValidationData(time(), $this->leeway); 
        $data->issuedBy($this->issuer);
        $data->permittedFor($this->audience);
        $data->identifiedBy($this->jti);
        $data->relatedTo($this->subject);

        return $this->decodeToken->validate($data);
    }

    /**
     * verify token
     */
    public function verify()
    {
        if (! $this->decodeToken) {
            return false;
        }
        
        try {
            list($signer, $secrect) = $this->getSigner(false);
        } catch(\Exception $e) {
            return false;
        }
        
        return $this->decodeToken->verify($signer, $secrect);
    }

    /**
     * 获取 decodeToken
     */
    public function getDecodeToken()
    {
        return $this->decodeToken;
    }
    
    /**
     * 获取 Header
     */
    public function getHeader($name)
    {
        if (! $this->decodeToken) {
            return false;
        }
        
        try {
            $header = $this->decodeToken->getHeader($name);
        } catch(\Exception $e) {
            return false;
        }
        
        return $header;
    }
    
    /**
     * 获取 Headers
     */
    public function getHeaders()
    {
        if (! $this->decodeToken) {
            return false;
        }
        
        return $this->decodeToken->getHeaders();
    }

    /**
     * 获取token存储数据
     */
    public function getClaim($name)
    {
        if (! $this->decodeToken) {
            return false;
        }
        
        try {
            $claim = $this->decodeToken->getClaim($name);
        } catch(\Exception $e) {
            return false;
        }
        
        return $claim;
    }
    
    /**
     * 获取 Claims
     */
    public function getClaims()
    {
        if (! $this->decodeToken) {
            return false;
        }
        
        $claims = $this->decodeToken->getClaims();
        
        $data = [];
        foreach ($claims as $claim) {
            $data[$claim->getName()] = $claimValue;
        }
        
        return $data;
    }
    
    /**
     * 加密载荷数据
     */
    public function withData($claim, $value = null)
    {
        if (is_array($claim)) {
            foreach ($claim as $k => $v) {
                $this->withData($k, $v);
            }
            
            return $this;
        }
        
        if (! empty($claim) && ! empty($value)) {
            $value = Crypt::encrypt($value, $this->passphrase);
            $this->withClaim($claim, $value);
        }
        
        return $this;
    }

    /**
     * 载荷解密后数据
     */
    public function getData($name)
    {
        $claim = $this->getClaim($name);
        if ($claim !== false) {
            $claim = Crypt::decrypt($claim, $this->passphrase);
        }
        
        return $claim;
    }

}
