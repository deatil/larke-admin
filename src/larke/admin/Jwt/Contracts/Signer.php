<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Contracts;

use Larke\JWT\Contracts\Key as KeyContract;
use Larke\JWT\Contracts\Signer as SignerContract;

/*
 * 签名
 *
 * @create 2023-2-4
 * @author deatil
 */
interface Signer
{
    /**
     * 签名类
     *
     * @return SignerContract
     */
    public function getSigner(): SignerContract;
    
    /**
     * 签名密钥
     *
     * @return KeyContract
     */
    public function getSignSecrect(): KeyContract;
    
    /**
     * 验证密钥
     *
     * @return KeyContract
     */
    public function getVerifySecrect(): KeyContract;
}
