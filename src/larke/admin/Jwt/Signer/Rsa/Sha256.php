<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Signer\Rsa;

use Larke\JWT\Signer\Rsa as RsaSigner; 
use Larke\Admin\Jwt\Signer\Rsa;

/*
 * Rsa Sha256 签名
 *
 * @create 2023-2-4
 * @author deatil
 */
class Sha256 extends Rsa
{
    /**
     * 签名方法
     */
    protected $signingMethod = RsaSigner\Sha256::class;
}
