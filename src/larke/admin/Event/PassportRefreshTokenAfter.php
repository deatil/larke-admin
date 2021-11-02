<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

/*
 * 刷新TOKEN后
 *
 * @create 2020-11-10
 * @author deatil
 */
class PassportRefreshTokenAfter
{
    /**
     * 存储登陆成功生成的 access_token, expires_in
     * 
     * @var array
     */
    public $jwt;
    
    /**
     * 构造方法
     * 
     * @access public
     */
    public function __construct(array $jwt)
    {
        // jwt 数据
        $this->jwt = $jwt;
    }
    
}
