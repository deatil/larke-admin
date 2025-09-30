<?php

declare (strict_types = 1);

namespace Larke\Admin;

use Larke\Admin\Extension\Manager;
use Larke\Admin\Auth\Admin as AuthAdmin;

/**
 * Admin
 *
 * @create 2024-7-31
 * @author deatil
 */
class Admin
{
    /**
     * 版本号
     */
    const VERSION = "2.2.1";
    
    /**
     * 发布号
     */
    const RELEASE = "20250930";
    
    /**
     * 扩展
     *
     * @return string
     */
    public static function extension(): Manager
    {
        return app("larke-admin.extension");
    }
    
    /**
     * 登录信息
     *
     * @return string
     */
    public static function authAdmin(): AuthAdmin
    {
        return app("larke-admin.auth-admin");
    }

}