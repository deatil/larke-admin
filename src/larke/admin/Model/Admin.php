<?php

namespace Larke\Admin\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/*
 * Admin 模型
 *
 * @create 2020-10-19
 * @author deatil
 */
class Admin extends Model
{
    protected $table = 'larke_admin';
    protected $pk = 'id';
    
    public $timestamps = false;
}
