<?php

declare (strict_types = 1);

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
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * 黑名单
     *
     * @var array
     */
    protected $guarded = [
        'is_root'
    ];
    
    /**
     * 隐藏
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'password_salt',
    ];
    
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
        return $this->morphMany(Attachment::class, 'attachmentable', 'belong_type', 'belong_id');
    }
    
    public function getAvatarAttribute($value) 
    {
        $attach = Attachment::path($value);
        
        return $attach;
    }
    
    public function scopeWithAccess($query, Array $ids = [])
    {
        return $query->with(['groupAccesses' => function ($query) use ($ids) {
            if (! app('larke-admin.auth-admin')->isAdministrator()) {
                $groupids = app('larke-admin.auth-admin')->getGroupChildrenIds();
                $query->whereIn('group_id', $groupids);
                
                if (! empty($ids)) {
                    $query->whereIn('group_id', $ids);
                }
            }
        }]);
    }
    
    /**
     * 更新头像
     */
    public function updateAvatar($data) 
    {
        return $this->update([
            'avatar' => $data,
        ]);
    }
    
}
