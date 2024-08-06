<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Signer\Rsa;

use Larke\JWT\Signer\Rsa as RsaSigner; 
use Larke\Admin\Jwt\Signer\Rsa;

/*
 * Rsa Sha384 签名
 *
 * @create 2023-2-4
 * @author deatil
 */
class Sha384 extends Rsa
{
    /**
     * 签名方法
     */
    protected string $signingMethod = RsaSigner\Sha384::class;
}
