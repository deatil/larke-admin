<?php

namespace Larke\Admin\Model;

use Illuminate\Database\Eloquent\Model;

/*
 * 登陆日志
 *
 * @create 2020-10-19
 * @author deatil
 */
class Log extends Model
{
    protected $table = 'larke_log';
    protected $keyType = 'string';
    protected $pk = 'id';
    
    public $timestamps = false;
    
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
            'login_time' => time(),
            'login_ip' => request()->ip(),
            'login_useragent' => request()->server('HTTP_USER_AGENT'),
            'login_referer' => request()->server('HTTP_REFERER'),
            'add_time' => time(),
            'add_ip' => request()->ip(),
        ], $data);
        self::create($data);
    }

}