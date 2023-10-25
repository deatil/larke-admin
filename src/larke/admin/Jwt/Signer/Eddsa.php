<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Signer;

use Illuminate\Support\Collection;

use Larke\JWT\Signer\Key\InMemory;
use Larke\JWT\Signer\Eddsa as EddsaSigner; 
use Larke\JWT\Contracts\Key as KeyContract;
use Larke\JWT\Contracts\Signer as SignerContract;

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
    protected string $signingMethod = EddsaSigner::class;
    
    /**
     * 配置
     *
     * @var Collection
     */
    private Collection $config;
    
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
    public function getSigner(): SignerContract
    {
        return new $this->signingMethod();
    }
    
    /**
     * 签名密钥
     *
     * @return \Larke\JWT\Contracts\Key
     */
    public function getSignSecrect(): KeyContract
    {
        $privateKey = $this->config->get("private_key");
        $privateKey = $this->formatSignSecrect($privateKey);
        
        $secrect = InMemory::base64Encoded($privateKey);
        
        return $secrect;
    }
    
    /**
     * 验证密钥
     *
     * @return \Larke\JWT\Contracts\Key
     */
    public function getVerifySecrect(): KeyContract
    {
        $publicKey = $this->config->get("public_key");
        $publicKey = $this->formatVerifySecrect($publicKey);
        
        $secrect = InMemory::base64Encoded($publicKey);
        
        return $secrect;
    }
    
    private function formatSignSecrect(string $key): string
    {
        if (file_exists($key)) {
            return file_get_contents($key);
        }
        
        return $key;
    }
    
    private function formatVerifySecrect(string $key): string
    {
        if (file_exists($key)) {
            return file_get_contents($key);
        }
        
        return $key;
    }
}
