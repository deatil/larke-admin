<?php

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
    
    public $incrementing = false;
    public $timestamps = false;
    
    public function setUrlAttribute($value) 
    {
        $this->attributes['id'] = md5(mt_rand(100000, 999999).microtime());
        $this->attributes['url'] = $value;
    }
    
    /**
     * 日志用户
     *
     * @create 2020-10-19
     * @author deatil
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }
    
    /**
     * 记录日志
     *
     * @create 2020-10-19
     * @author deatil
     */
    public static function record($data = [])
    {
        $data = array_merge([
            'id' => md5(mt_rand(100000, 999999).microtime()),
            'method' => app()->request->method(),
            'url' => urldecode(request()->getUri()),
            'ip' => request()->ip(),
            'useragent' => request()->server('HTTP_USER_AGENT'),
            'add_time' => time(),
            'add_ip' => request()->ip(),
        ], $data);
        self::insert($data);
    }

}