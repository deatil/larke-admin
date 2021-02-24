<?php

declare (strict_types = 1);

namespace Larke\Admin\Auth;

use Enforcer;

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
    public static function addRoleForUser(string $user, string $role)
    {
        return Enforcer::addRoleForUser($user, $role);
    }
    
    /**
     * 用户角色是否拥有某角色
     */
    public static function hasRoleForUser(string $user, string $role)
    {
        return Enforcer::hasRoleForUser($user, $role);
    }
    
    /**
     * 删除用户角色
     */
    public static function deleteRoleForUser(string $user, string $role)
    {
        return Enforcer::deleteRoleForUser($user, $role);
    }
    
    /**
     * 删除用户所有角色
     */
    public static function deleteRolesForUser(string $user)
    {
        return Enforcer::deleteRolesForUser($user);
    }
    
    /**
     * 删除用户信息
     */
    public static function deleteUser(string $user)
    {
        return Enforcer::deleteUser($user);
    }
    
    /**
     * 添加权限
     */
    public static function addPolicy(string $name, string $type, string $rule)
    {
        return Enforcer::addPolicy($name, $type, $rule);
    }
    
    /**
     * 删除权限
     */
    public static function deletePolicy(string $name, string $type, string $rule)
    {
        return Enforcer::deletePermissionForUser($name, $type, $rule);
    }
    
    /**
     * 删除标识所有权限
     */
    public static function deletePolicys(string $name)
    {
        return Enforcer::deletePermissionForUser($name);
    }
    
    /**
     * 判断是否有权限
     */
    public static function hasPermissionForUser(string $name, string $type, string $rule)
    {
        return Enforcer::hasPermissionForUser($name, $type, $rule);
    }
    
    /**
     * 全部权限
     */
    public static function getPermissionsForUser(string $user)
    {
        return Enforcer::getPermissionsForUser($user);
    }
    
    /**
     * 验证用户权限
     */
    public static function enforce(string $user, string $type, string $rule)
    {
        return Enforcer::enforce($user, $type, $rule);
    }
}
