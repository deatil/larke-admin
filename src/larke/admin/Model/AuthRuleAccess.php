<?php

namespace Larke\Admin\Model;

/*
 * AuthRuleAccess
 *
 * @create 2020-10-20
 * @author deatil
 */
class AuthRuleAccess extends Base
{
    protected $table = 'larke_auth_rule_access';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    public $incrementing = false;
    public $timestamps = false;
}