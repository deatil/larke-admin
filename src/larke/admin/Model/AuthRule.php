<?php

namespace Larke\Admin\Model;

/*
 * AuthRule
 *
 * @create 2020-10-20
 * @author deatil
 */
class AuthRule extends Base
{
    protected $table = 'larke_auth_rule';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * 规则的分组列表
     *
     * @create 2020-10-20
     * @author deatil
     */
    public function groups()
    {
        return $this->belongsToMany(AuthGroup::class, AuthRuleAccess::class, 'rule_id', 'group_id');
    }
    
    /**
     * 授权
     */
    public function ruleAccess()
    {
        return $this->hasOne(AuthRuleAccess::class, 'rule_id', 'id');
    }
    
    /**
     * 获取子模块
     */
    public function childrenModule()
    {
        return $this->hasMany($this, 'parentid', 'id');
    }
    
    /**
     * 递归获取子模块
     */
    public function children()
    {
        return $this->childrenModule()->with('children');
    }
}