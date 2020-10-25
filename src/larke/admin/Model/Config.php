<?php

namespace Larke\Admin\Model;

/*
 * Config
 *
 * @create 2020-10-24
 * @author deatil
 */
class Config extends Base
{
    protected $table = 'larke_config';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    public $incrementing = false;
    public $timestamps = false;
}