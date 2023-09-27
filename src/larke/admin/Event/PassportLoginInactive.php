<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

use Larke\Admin\Model\Admin as AdminModel;

/**
 * 账号被禁用
 *
 * @create 2021-2-25
 * @author deatil
 */
class PassportLoginInactive
{
    /**
     * Request 实例
     * 
     * @var \Larke\Admin\Model\Admin
     */
    public AdminModel $admin;
    
    /**
     * 构造方法
     * 
     * @access public
     * @param  \Larke\Admin\Model\Admin  $admin
     */
    public function __construct(AdminModel $admin)
    {
        $this->admin = $admin;
    }
    
}
