<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Signer;

use Illuminate\Support\Collection;

use Larke\JWT\Signer\Ecdsa as EcdsaSigner; 
use Larke\JWT\Signer\Key\InMemory;
use Larke\JWT\Signer\Key\LocalFileReference;

use Larke\Admin\Jwt\Contracts\Signer;

/*
 * Ecdsa 签名
 *
 * @create 2023-2-4
 * @author deatil
 */
class Ecdsa implements Signer
{
    /**
     * 签名方法
     */
    protected $signingMethod = EcdsaSigner\Sha256::class;
    
    /**
     * 配置
     *
     * @var Collection
     */
    private $config = [];
    
    /**
     * 构造方法
     * 
     * @param Collection $config 配置信息
     */
    public function __construct(Collection $config)
    {
        $this->config = $config;
    }
    
    /**
     * 签名类
     *
     * @return \Larke\JWT\Contracts\Signer
     */
    public function getSigner() 
    {
        return new $this->signingMethod();
    }
    
    /**
     * 签名密钥
     *
     * @return string
     */
    public function getSignSecrect() 
    {
        $privateKey = $this->config->get("private_key");
        
        $passphrase = $this->config->get("passphrase");
        if (! empty($passphrase)) {
            $passphrase = InMemory::base64Encoded($passphrase)->getContent();
        }
        
        $secrect = LocalFileReference::file($privateKey, $passphrase);
        
        return $secrect;
    }
    
    /**
     * 验证密钥
     *
     * @return string
     */
    public function getVerifySecrect() 
    {
        $publicKey = $this->config->get("public_key");
        $secrect = LocalFileReference::file($publicKey);
        
        return $secrect;
    }
}
