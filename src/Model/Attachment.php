<?php

declare (strict_types = 1);

namespace Larke\Admin\Model;

use Larke\Admin\Service\Upload;

/**
 * 附件模型
 *
 * @create 2020-10-19
 * @author deatil
 */
class Attachment extends Base
{
    protected $table = 'larke_attachment';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    protected $appends = [
        'url',
    ];
    
    public $incrementing = false;
    public $timestamps = false;
    
    public function getUrlAttribute() 
    {
        $value = $this->path;
        if (empty($value)) {
            return '';
        }
        
        $upload = Upload::driver($this->driver);
        if ($upload === false) {
            return '';
        }
        
        return $upload->objectUrl($value);
    }
    
    public function attachmentable()
    {
        return $this->morphTo(__FUNCTION__, 'belong_type', 'belong_id');
    }
    
    /**
     * md5
     */
    public function scopeByMd5($query, $md5)
    {
        return $query->where('md5', '=', $md5);
    }
    
    /**
     * sha1
     */
    public function scopeBySha1($query, $sha1)
    {
        return $query->where('sha1', '=', $sha1);
    }
    
    /**
     * 快捷查询地址
     */
    public static function path($id, ?mixed $default = null)
    {
        return static::where('id', $id)
            ->first()
            ->url ?? $default;
    }

}

