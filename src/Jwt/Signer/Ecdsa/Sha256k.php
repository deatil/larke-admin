<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Signer\Ecdsa;

use Larke\JWT\Signer\Ecdsa as EcdsaSigner; 
use Larke\Admin\Jwt\Signer\Ecdsa;

/*
 * Ecdsa Sha256 签名
 *
 * @create 2025-2-25
 * @author deatil
 */
class Sha256k extends Ecdsa
{
    /**
     * 签名方法
     */
    protected string $signingMethod = EcdsaSigner\Sha256k::class;
}
