<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Signer;

use Illuminate\Support\Collection;

use Larke\JWT\Signer\Key\InMemory;
use Larke\JWT\Signer\Eddsa as EddsaSigner; 

use Larke\Admin\Jwt\Contracts\Signer;

/*
 * Eddsa 签名
 *
 * @create 2023-2-4
 * @author deatil
 */
class Eddsa implements Signer
{
    /**
     * 签名方法
     */
    protected $signingMethod = EddsaSigner::class;
    
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
        
        $secrect = InMemory::base64Encoded($privateKey)->getContent();
        $secrect = InMemory::plainText($secrect);
        
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
        
        $secrect = InMemory::base64Encoded($publicKey)->getContent();
        $secrect = InMemory::plainText($secrect);
        
        return $secrect;
    }
}
