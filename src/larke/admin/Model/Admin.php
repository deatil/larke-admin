<?php

namespace Larke\Admin\Model;

use Illuminate\Support\Facades\Storage;

/*
 * Admin 模型
 *
 * @create 2020-10-19
 * @author deatil
 */
class Admin extends Base
{
    protected $table = 'larke_admin';
    protected $primaryKey = 'id';
    
    public $incrementing = false;
    public $timestamps = false;
    
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable', 'type', 'type_id');
    }
    
    public function getAvatarAttribute($value) 
    {
        $attach = Attachment::path($value);
        if (empty($attach)) {
            return '';
        }
        
        $avatar = Storage::url($attach);
        return $avatar;
    }
    
}
