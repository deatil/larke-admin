<?php

declare (strict_types = 1);

namespace Larke\Admin\Support;

/**
 * 密码
 *
 * @create 2020-10-19
 * @author deatil
 */
class Password
{
    protected $salt = '';
    
    /**
     * 设置盐
     *
     * @param   $salt   加密盐
     * @return  $this
     */
    public function withSalt(string $salt)
    {
        $this->salt = $salt;
        return $this;
    }
    
    /**
     * 密码加密
     *
     * @param   $password   密码
     * @param   $encrypt    传入加密串，在修改密码时做认证
     * @return  array/password
     */
    public function encrypt(string $password, string $encrypt = '')
    {
        $pwd = [];
        $pwd['encrypt'] = $encrypt ? $encrypt : $this->randomString();
        $pwd['password'] = md5(md5($password . $pwd['encrypt']) . $this->salt);
        return $encrypt ? $pwd['password'] : $pwd;
    }
    
    /**
     * 随机字符串
     *
     * @param   type $len 字符长度
     * @return  string 随机字符串
     */
    protected function randomString(int $len = 6)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, intval(ceil($len / strlen($pool))))), 0, $len);
    }

}
