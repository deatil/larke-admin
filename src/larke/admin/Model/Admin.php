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
    protected $primaryKey = 'id';
    
    public $incrementing = false;
    public $timestamps = false;
    
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable', 'type', 'type_id');
    }
}
