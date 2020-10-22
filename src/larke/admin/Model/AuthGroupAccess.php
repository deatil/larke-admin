<?php

namespace Larke\Admin\Model;

use Illuminate\Database\Eloquent\Model;

/*
 * AuthGroupAccess
 *
 * @create 2020-10-20
 * @author deatil
 */
class AuthGroupAccess extends Model
{
    protected $table = 'larke_auth_group_access';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    public $incrementing = false;
    public $timestamps = false;
}