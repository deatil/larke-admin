<?php

namespace Larke\Admin\Model;

/*
 * Extension
 *
 * @create 2020-10-30
 * @author deatil
 */
class Extension extends Base
{
    protected $table = 'larke_extension';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    public $incrementing = false;
    public $timestamps = false;
}