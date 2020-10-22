<?php

namespace Larke\Admin\Model;

use Illuminate\Database\Eloquent\Model;

/*
 * AuthRule
 *
 * @create 2020-10-20
 * @author deatil
 */
class AuthRule extends Model
{
    protected $table = 'larke_auth_rule';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
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
        return $this->belongsToMany(AuthGroup::class, AuthRuleAccess::class, 'group_id', 'rule_id');
    }
}