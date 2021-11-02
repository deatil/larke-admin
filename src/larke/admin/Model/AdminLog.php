<?php

declare (strict_types = 1);

namespace Larke\Admin\Model;

/*
 * 登陆日志
 *
 * @create 2020-10-19
 * @author deatil
 */
class AdminLog extends Base
{
    protected $table = 'larke_admin_log';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    public $incrementing = false;
    public $timestamps = false;
    
    public function setUrlAttribute($value) 
    {
        $this->attributes['url'] = $value;
    }
    
    /**
     * 日志用户
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }
    
    /**
     * 记录日志
     */
    public static function record($data = [])
    {
        $data = array_merge([
            'method' => app()->request->method(),
            'url' => urldecode(request()->getUri()),
            'ip' => request()->ip(),
            'useragent' => request()->server('HTTP_USER_AGENT'),
        ], $data);
        
        self::create($data);
    }

}