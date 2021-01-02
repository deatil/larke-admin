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
    
    public static function path($id, $default = null)
    {
        return static::where('id', $id)
            ->first()
            ->url ?? $default;
    }

}

