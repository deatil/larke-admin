<?php

declare (strict_types = 1);

namespace Larke\Admin\Auth;

/**
 * 权限
 *
 * @create 2020-12-24
 * @author deatil
 */
class Permission
{
    /**
     * 添加用户角色
     */
    public static function addRoleForUser($user, $role)
    {
        \Enforcer::addRoleForUser($user, $role);
    }
    
    /**
     * 删除用户角色
     */
    public static function deleteRoleForUser($user, $role)
    {
        \Enforcer::deleteRoleForUser($user, $role);
    }
    
    /**
     * 删除用户所有角色
     */
    public static function deleteRolesForUser($user)
    {
        \Enforcer::deleteRolesForUser($user);
    }
    
    /**
     * 添加权限
     */
    public static function addPolicy($name, $type, $rule)
    {
        \Enforcer::addPolicy($name, $type, $rule);
    }
    
    /**
     * 删除权限
     */
    public static function deletePolicy($name, $type, $rule)
    {
        \Enforcer::deletePermissionForUser($name, $type, $rule);
    }
    
    /**
     * 删除标识所有权限
     */
    public static function deletePolicys($name)
    {
        \Enforcer::deletePermissionForUser($name);
    }
    
    /**
     * 判断是否有权限
     */
    public static function hasPermissionForUser($name, $type, $rule)
    {
        \Enforcer::hasPermissionForUser($name, $type, $rule);
    }
    
    /**
     * 验证用户权限
     */
    public static function enforce($user, $type, $rule)
    {
        \Enforcer::enforce($user, $type, $rule);
    }
}
