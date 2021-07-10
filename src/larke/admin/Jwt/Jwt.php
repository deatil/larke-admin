<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use Larke\JWT\Builder;
use Larke\JWT\Parser;
use Larke\JWT\Signer\Key\InMemory;
use Larke\JWT\Signer\Key\LocalFileReference;
use Larke\JWT\ValidationData;
use Larke\JWT\Signer; // 文件夹

use Larke\Admin\Exception\JWTException;
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
     * jwt claims
     */
    private $claims = [];
    
    /**
     * jwt enToken
     */
    private $enToken = '';
    
    /**
     * jwt deToken
     */
    private $deToken = '';
    
    /**
     * parseToken
     */
    private $parseToken;
    
    /**
     * 配置
     */
    private $signerConfig = [];
    
    /**
     * 类型列表
     */
    protected $algorithms = [
        // Hmac 加密
        'HS256' => Signer\Hmac\Sha256::class,
        'HS384' => Signer\Hmac\Sha384::class,
        'HS512' => Signer\Hmac\Sha512::class,
        
        // Rsa 加密
        'RS256' => Signer\Rsa\Sha256::class,
        'RS384' => Signer\Rsa\Sha384::class,
        'RS512' => Signer\Rsa\Sha512::class,
        
        // Ecdsa 加密
        'ES256' => Signer\Ecdsa\Sha256::class,
        'ES384' => Signer\Ecdsa\Sha384::class,
        'ES512' => Signer\Ecdsa\Sha512::class,
        
        // Eddsa 加密
        'EdDSA' => Signer\Eddsa::class,
    ];
    
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
     * 设置 enToken
     */
    public function withEnToken($enToken)
    {
        $this->enToken = $enToken;
        return $this;
    }
    
    /**
     * 获取 enToken
     */
    public function getEnToken()
    {
        return (string) $this->enToken;
    }
    
    /**
     * 设置 deToken
     */
    public function withDeToken($deToken)
    {
        $this->deToken = $deToken;
        return $this;
    }
    
    /**
     * 获取 deToken
     */
    public function getDeToken()
    {
        return (string) $this->deToken;
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
        
        // 加密方式
        $algorithm = Arr::get($config, 'algorithm', 'HS256');
        if (empty($algorithm)) {
            Log::error('larke-admin-jwt-signer: 加密方式为空');
            
            throw new JWTException(__('JWT编码失败'));
        }
        
        // 加密方式不存在
        if (! array_key_exists($algorithm, $this->algorithms)) {
            Log::error('larke-admin-jwt-signer: ' . $algorithm . ' 加密方式不存在');
            
            throw new JWTException(__('JWT编码失败'));
        }
        
        // 加密方式
        $signer = new $this->algorithms[$algorithm];
        
        // 加密秘钥
        $secrect = '';
        switch ($algorithm) {
            case 'HS256':
            case 'HS384':
            case 'HS512':
                $key = Arr::get($config, 'hmac.secrect', '');
                $secrect = InMemory::plainText($key);
                break;
            case 'RS256':
            case 'RS384':
            case 'RS512':
                if ($isPrivate) {
                    $privateKey = Arr::get($config, 'rsa.private_key', '');
                    
                    $passphrase = Arr::get($config, 'rsa.passphrase', null);
                    if (! empty($passphrase)) {
                        $passphrase = InMemory::base64Encoded($passphrase)->getContent();
                    }
                    
                    $secrect = LocalFileReference::file($privateKey, $passphrase);
                } else {
                    $publicKey = Arr::get($config, 'rsa.public_key', '');
                    $secrect = LocalFileReference::file($publicKey);
                }
                break;
            case 'ES256':
            case 'ES384':
            case 'ES512':
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
            case 'EdDSA':
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
        $builder = new Builder();
        
        $builder->issuedBy($this->issuer); // 发布者
        $builder->permittedFor($this->audience); // 接收者
        $builder->relatedTo($this->subject); // 主题
        $builder->identifiedBy($this->jti); // 对当前token设置的标识
        
        $time = time();
        $builder->issuedAt($time); // token创建时间
        $builder->canOnlyBeUsedAfter($time + $this->notBeforeTime); // 多少秒内无法使用
        $builder->expiresAt($time + $this->expTime); // 过期时间
        
        foreach ($this->headers as $headerKey => $header) {
            $builder->withHeader($headerKey, $header);
        }
        
        foreach ($this->claims as $claimKey => $claim) {
            $builder->withClaim($claimKey, $claim);
        }
        
        try {
            list ($signer, $secrect) = $this->getSigner(true);
            
            $this->enToken = $builder->getToken($signer, $secrect);
        } catch(\Exception $e) {
            Log::error('larke-admin-jwt-encode: '.$e->getMessage());
            
            throw new JWTException(__('JWT编码失败'));
        }
        
        return $this;
    }
    
    /**
     * 解码
     */
    public function decode()
    {
        try {
            $this->parseToken = (new Parser())->parse((string) $this->deToken); 
        } catch(\Exception $e) {
            Log::error('larke-admin-jwt-decode: '.$e->getMessage());
            
            throw new JWTException(__('JWT解析失败'));
        }
        
        return $this;
    }
    
    /**
     * 验证
     */
    public function validate()
    {
        $data = new ValidationData(time(), $this->leeway); 
        $data->issuedBy($this->issuer);
        $data->permittedFor($this->audience);
        $data->identifiedBy($this->jti);
        $data->relatedTo($this->subject);
        
        return $this->parseToken->validate($data);
    }

    /**
     * 检测
     */
    public function verify()
    {
        list ($signer, $secrect) = $this->getSigner(false);
    
        return $this->parseToken->verify($signer, $secrect);
    }

    /**
     * 获取 parseToken
     */
    public function getParseToken()
    {
        return $this->parseToken;
    }
    
    /**
     * 获取 Header
     */
    public function getHeader($name)
    {
        $header = $this->parseToken->getHeader($name);
        
        return $header;
    }
    
    /**
     * 获取 Headers
     */
    public function getHeaders()
    {
        return $this->parseToken->getHeaders();
    }

    /**
     * 获取token存储数据
     */
    public function getClaim($name)
    {
        $claim = $this->parseToken->getClaim($name);
        
        return $claim;
    }
    
    /**
     * 获取 Claims
     */
    public function getClaims()
    {
        $claims = $this->parseToken->getClaims();
        
        $data = [];
        foreach ($claims as $claim) {
            $data[$claim->getName()] = $claim->getValue();
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
            $value = (new Crypt())->encrypt($value, $this->base64Decode($this->passphrase));
            
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
        
        $claim = (new Crypt())->decrypt($claim, $this->base64Decode($this->passphrase));
        
        return $claim;
    }
    
    /**
     * base64解密
     */
    public function base64Decode($contents)
    {
        if (empty($contents)) {
            return '';
        }
        
        $decoded = base64_decode($contents, true);
        
        if ($decoded === false) {
            throw new JWTException(__('JWT载荷解析失败'));
        }
        
        return $decoded;
    }
}
