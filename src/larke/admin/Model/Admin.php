<?php

namespace Larke\Admin\Model;

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
    
    protected $guarded = [];
    
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * 授权
     */
    public function groupAccesses()
    {
        return $this->hasMany(AuthGroupAccess::class, 'admin_id', 'id');
    }
    
    /**
     * 分组列表
     */
    public function groups()
    {
        return $this->belongsToMany(AuthGroup::class, AuthGroupAccess::class, 'admin_id', 'group_id');
    }
    
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable', 'type', 'type_id');
    }
    
    public function getAvatarAttribute($value) 
    {
        $attach = Attachment::path($value);
        
        return $attach;
    }
    
}