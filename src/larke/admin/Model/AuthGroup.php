<?php

namespace Larke\Admin\Model;

/*
 * AuthGroup
 *
 * @create 2020-10-20
 * @author deatil
 */
class AuthGroup extends Base
{
    protected $table = 'larke_auth_group';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * 组的规则授权
     */
    public function ruleAccesses()
    {
        return $this->hasMany(AuthRuleAccess::class, 'group_id', 'id');
    }
    
    /**
     * 组的权限列表
     */
    public function rules()
    {
        return $this->belongsToMany(AuthRule::class, AuthRuleAccess::class, 'rule_id', 'group_id');
    }
    
    /**
     * 组的分组授权
     */
    public function groupAccess()
    {
        return $this->hasOne(AuthGroupAccess::class, 'group_id', 'id');
    }
    
    /**
     * 组的管理员列表
     */
    public function admins()
    {
        return $this->belongsToMany(Admin::class, AuthGroupAccess::class, 'admin_id', 'group_id');
    }
}