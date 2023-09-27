<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

use Larke\Admin\Model\Admin as AdminModel;

/**
 * 登陆之后
 *
 * @create 2020-11-2
 * @author deatil
 */
class PassportLoginAfter
{
    /**
     * Request 实例
     * 
     * @var \Larke\Admin\Model\Admin
     */
    public AdminModel $admin;
    
    /**
     * 存储登陆成功生成的 access_token, expires_in, refresh_token
     * 
     * @var array
     */
    public array $jwt;
    
    /**
     * 构造方法
     * 
     * @access public
     * @param  \Larke\Admin\Model\Admin  $admin
     */
    public function __construct(AdminModel $admin, array $jwt)
    {
        $this->admin = $admin;
        
        // jwt 数据
        $this->jwt = $jwt;
    }
    
}
