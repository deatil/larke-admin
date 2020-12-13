<?php

namespace Larke\Admin\Model;

use Larke\Admin\Service\Upload as UploadService;

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
        
        return (new UploadService())
            ->disk($this->driver)
            ->objectUrl($value);
    }
    
    public function attachmentable()
    {
        return $this->morphTo(__FUNCTION__, 'belong_type', 'belong_id');
    }
    
    public static function path($id)
    {
        $data = self::where('id', $id)
            ->select('path', 'driver')
            ->first();
        if (empty($data)) {
            return '';
        }
        
        return (new UploadService())
            ->disk($data->driver)
            ->objectUrl($data->path);
    }

}

