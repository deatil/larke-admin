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
        $privateKey = $this->formatSignSecrect($privateKey);
        
        $secrect = InMemory::base64Encoded($privateKey);
        
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
        $publicKey = $this->formatSignSecrect($publicKey);
        
        $secrect = InMemory::base64Encoded($publicKey);
        
        return $secrect;
    }
    
    private function formatSignSecrect(string $key)
    {
        if (file_exists($key)) {
            // key 需为解析出的 der 数据
            $secretkey = sodium_crypto_sign_secretkey(
                sodium_crypto_sign_seed_keypair(
                    file_get_contents($key)
                )
            );
            
            return base64_encode($secretkey);
        }
        
        return $key;
    }
    
    private function formatVerifySecrect(string $key)
    {
        if (file_exists($key)) {
            // key 需为解析出的 der 数据
            $publickey = sodium_crypto_sign_publickey(
                sodium_crypto_sign_seed_keypair(
                    file_get_contents($key)
                )
            );
            
            return base64_encode($publickey);
        }
        
        return $key;
    }

}
