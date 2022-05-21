<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use Larke\Admin\Exception\JWTException;
use Larke\Admin\Contracts\Jwt as JwtContract;
use Larke\Admin\Contracts\Crypt as CryptContract;

/**
 * jwt 管理器
 *
 * @create 2022-3-13
 * @author deatil
 */
class JwtManager implements JwtContract
{
    /**
     * jwt
     */
    private $jwt = null;
    
    /**
     * 加密
     */
    private $crypt = null;
    
    /**
     * 配置
     */
    private $config = [];
    
    /**
     * headers
     */
    private $headers = [];
    
    /**
     * 载荷
     */
    private $claims = [];
    
    /**
     * 生成的 token
     */
    private $enToken = '';
    
    /**
     * 需要解析的 token
     */
    private $deToken = '';
    
    /**
     * 解析后的 token 句柄
     */
    private $parseToken;
    
    /**
     * 设置 jwt
     */
    public function withJwt($jwt)
    {
        $this->jwt = $jwt;
        return $this;
    }
    
    /**
     * 获取 jwt
     */
    public function getJwt()
    {
        return $this->jwt;
    }
    
    /**
     * 设置加密
     */
    public function withCrypt(CryptContract $crypt)
    {
        $this->crypt = $crypt;
        return $this;
    }
    
    /**
     * 获取加密
     */
    public function getCrypt()
    {
        return $this->crypt;
    }
    
    /**
     * 设置配置
     */
    public function withConfig($key, $value)
    {
        $this->config[$key] = $value;
        return $this;
    }
    
    /**
     * 设置配置
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }
    
    /**
     * 获取配置
     */
    public function getConfig()
    {
        return $this->config;
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
        $this->config['iss'] = $issuer;
        return $this;
    }
    
    /**
     * 设置 aud
     */
    public function withAud($audience)
    {
        $this->config['aud'] = $audience;
        return $this;
    }
    
    /**
     * 设置 subject
     */
    public function withSub($subject)
    {
        $this->config['sub'] = $subject;
        return $this;
    }
    
    /**
     * 设置 jti
     */
    public function withJti($jti)
    {
        $this->config['jti'] = $jti;
        return $this;
    }
    
    /**
     * 设置 issuedAt
     */
    public function withIat($issuedAt)
    {
        $this->config['iat'] = $issuedAt;
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
        
        $this->config['nbf'] = $notBeforeTime;
        return $this;
    }
    
    /**
     * 设置 expTime
     */
    public function withExp($expTime)
    {
        $this->config['exp'] = $expTime;
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
     * 设置 JWT 配置
     */
    public function putJwtConfig()
    {
        // 配置
        $config = $this->config;
        
        // 发布者
        $this->jwt->withIss(Arr::get($config, 'iss', '')); 
        // 接收者
        $this->jwt->withAud(Arr::get($config, 'aud', '')); 
        // 主题
        $this->jwt->withSub(Arr::get($config, 'sub', '')); 
        // 对当前token设置的标识
        $this->jwt->withJti(Arr::get($config, 'jti', '')); 
        
        $iat = Arr::get($config, 'iat', 0);
        if (empty($iat)) {
            $iat = time();
        }
        
        // token创建时间
        $this->jwt->withIat($iat); 
        // 多少秒内无法使用
        $this->jwt->withNbf(Arr::get($config, 'nbf', 0)); 
        // 过期时间
        $this->jwt->withExp(Arr::get($config, 'exp', 0)); 
        // leeway
        $this->jwt->withLeeway(Arr::get($config, 'leeway', 0)); 
        
        return $this;
    }
    
    /**
     * 设置签名
     */
    public function setSigner($isPrivate = true)
    {
        $config = Arr::get($this->config, 'signer', []);;
        
        // 加密方式
        $algorithm = Arr::get($config, 'algorithm', 'HS256');
        if (empty($algorithm)) {
            Log::error('larke-admin-jwt-signer: 加密方式为空');
            
            throw new JWTException(__('JWT编码失败'));
        }
        
        $this->jwt->withSigningMethod($algorithm);
        
        // 加密秘钥
        switch ($algorithm) {
            case 'HS256':
            case 'HS384':
            case 'HS512':
                $secrect = Arr::get($config, 'hmac.secrect', '');
                
                $this->jwt->withSecret($secrect);
                
                break;
            case 'RS256':
            case 'RS384':
            case 'RS512':
                if ($isPrivate) {
                    $privateKey = Arr::get($config, 'rsa.private_key', '');
                    $passphrase = Arr::get($config, 'rsa.passphrase', null);
                    
                    $this->jwt->withPrivateKey($privateKey);
                    $this->jwt->withPrivateKeyPassword($passphrase);
                } else {
                    $publicKey = Arr::get($config, 'rsa.public_key', '');
                    
                    $this->jwt->withPublicKey($publicKey);
                }
                break;
            case 'ES256':
            case 'ES384':
            case 'ES512':
                if ($isPrivate) {
                    $privateKey = Arr::get($config, 'ecdsa.private_key', '');
                    $passphrase = Arr::get($config, 'ecdsa.passphrase', null);
                    
                    $this->jwt->withPrivateKey($privateKey);
                    $this->jwt->withPrivateKeyPassword($passphrase);
                } else {
                    $publicKey = Arr::get($config, 'ecdsa.public_key', '');
                    
                    $this->jwt->withPublicKey($publicKey);
                }
                break;
            case 'EdDSA':
                if ($isPrivate) {
                    $privateKey = Arr::get($config, 'eddsa.private_key', '');
                    
                    $this->jwt->withPrivateKey($privateKey);
                } else {
                    $publicKey = Arr::get($config, 'eddsa.public_key', '');
                    
                    $this->jwt->withPublicKey($publicKey);
                }
                break;
            default:
                // 加密方式不存在
                Log::error('larke-admin-jwt-signer: ' . $algorithm . ' 加密方式不存在');
                
                throw new JWTException(__('JWT编码失败'));
        }
        
        return $this;
    }
    
    /**
     * 编码 jwt token
     */
    public function encode()
    {
        $this->putJwtConfig()->setSigner(true);
        
        foreach ($this->headers as $headerKey => $header) {
            $this->jwt->withHeader($headerKey, $header);
        }
        
        foreach ($this->claims as $claimKey => $claim) {
            $this->jwt->withClaim($claimKey, $claim);
        }
        
        try {
            $this->enToken = $this->jwt->makeToken();
        } catch(\Exception $e) {
            Log::error('larke-admin-jwt-makeToken: '.$e->getMessage());
            
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
            $this->parseToken = $this->jwt->parseToken((string) $this->deToken); 
        } catch(\Exception $e) {
            Log::error('larke-admin-jwt-parseToken: '.$e->getMessage());
            
            throw new JWTException(__('JWT解析失败'));
        }
        
        return $this;
    }
    
    /**
     * 验证
     */
    public function validate()
    {
        $this->putJwtConfig();
        
        return $this->jwt->validate($this->parseToken);
    }

    /**
     * 检测
     */
    public function verify()
    {
        $this->putJwtConfig()->setSigner(false);
    
        return $this->jwt->verify($this->parseToken);
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
        return $this->jwt->getHeader($this->parseToken, $name);
    }
    
    /**
     * 获取 Headers
     */
    public function getHeaders()
    {
        return $this->jwt->getHeaders($this->parseToken);
    }

    /**
     * 获取 token 存储数据
     */
    public function getClaim($name)
    {
        return $this->jwt->getClaim($this->parseToken, $name);
    }
    
    /**
     * 获取 Claims
     */
    public function getClaims()
    {
        return $this->jwt->getClaims($this->parseToken);
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
            $value = $this->crypt->encrypt($value, $this->base64Decode(Arr::get($this->config, 'passphrase', '')));
            
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
        
        $claim = $this->crypt->decrypt($claim, $this->base64Decode(Arr::get($this->config, 'passphrase', '')));
        
        return $claim;
    }
    
    /**
     * base64 解密
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
