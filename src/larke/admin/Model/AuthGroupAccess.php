<?php

namespace Larke\Admin\Model;

/*
 * AuthGroupAccess
 *
 * @create 2020-10-20
 * @author deatil
 */
class AuthGroupAccess extends Base
{
    protected $table = 'larke_auth_group_access';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * 分组
     */
    public function group()
    {
        return $this->hasOne(AuthGroup::class, 'id', 'group_id');
    }
}