<?php

declare (strict_types = 1);

namespace Larke\Admin\Contracts;

/*
 * 加密契约
 *
 * @create 2022-3-13
 * @author deatil
 */
interface Crypt
{
    /**
     * 加密函数
     *
     * @param   string  $plaintext  需要加密的字符串
     * @param   string  $key        密钥
     * @return  string  返回加密结果
     */
    public function encrypt($plaintext, $key = '');
    
    /**
     * 解密函数
     *
     * @param   string  $plaintext  需要解密的字符串
     * @param   string  $key        密匙
     * @param   string  $ttl        过期时间
     * @return  string|null 字符串类型的返回结果
     */
    public function decrypt($plaintext, $key = '', $ttl = 0);
}
