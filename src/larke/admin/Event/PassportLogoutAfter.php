<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

/**
 * 退出后
 *
 * @create 2020-11-10
 * @author deatil
 */
class PassportLogoutAfter
{
    /**
     * 存储登陆成功生成的 access_token, expires_in, refresh_token
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
