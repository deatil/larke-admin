<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Signer\Ecdsa;

use Larke\JWT\Signer\Ecdsa as EcdsaSigner; 
use Larke\Admin\Jwt\Signer\Ecdsa;

/*
 * Ecdsa Sha256 签名
 *
 * @create 2023-2-4
 * @author deatil
 */
class Sha256 extends Ecdsa
{
    /**
     * 签名方法
     */
    protected string $signingMethod = EcdsaSigner\Sha256::class;
}
