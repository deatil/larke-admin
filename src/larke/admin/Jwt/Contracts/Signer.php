<?php

declare (strict_types = 1);

namespace Larke\Admin\Jwt\Contracts;

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
     * @return \Larke\JWT\Contracts\Signer
     */
    public function getSigner();
    
    /**
     * 签名密钥
     *
     * @return string
     */
    public function getSignSecrect();
    
    /**
     * 验证密钥
     *
     * @return string
     */
    public function getVerifySecrect();
}
