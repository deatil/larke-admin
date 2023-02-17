<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Signer;

use Illuminate\Support\Collection;

use Larke\JWT\Signer\Key\InMemory;
use Larke\JWT\Signer\None as NoneSigner; 
use Larke\JWT\Contracts\Key as KeyContract;
use Larke\JWT\Contracts\Signer as SignerContract;

use Larke\Admin\Jwt\Contracts\Signer;

/*
 * 空签名
 *
 * @create 2023-2-10
 * @author deatil
 */
class None implements Signer
{
    /**
     * 签名方法
     */
    protected string $signingMethod = NoneSigner::class;
    
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
     * @return SignerContract
     */
    public function getSigner(): SignerContract
    {
        return new $this->signingMethod();
    }
    
    /**
     * 签名密钥
     *
     * @return string
     */
    public function getSignSecrect(): KeyContract
    {
        return $this->getSecrect();
    }
    
    /**
     * 验证密钥
     *
     * @return string
     */
    public function getVerifySecrect(): KeyContract
    {
        return $this->getSecrect();
    }
    
    /**
     * 密钥
     *
     * @return string
     */
    private function getSecrect(): KeyContract
    {
        $secrect = $this->config->get("secrect");
        
        // base64 秘钥数据解码
        $secrect = InMemory::base64Encoded($secrect);

        return $secrect;
    }
    
}
