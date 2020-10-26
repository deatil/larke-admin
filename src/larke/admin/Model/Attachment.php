<?php

namespace Larke\Admin\Model;

use Illuminate\Support\Facades\Storage;

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
        'realpath',
    ];
    
    public $incrementing = false;
    public $timestamps = false;
    
    public function getRealpathAttribute() 
    {
        $value = $this->path;
        if (empty($value)) {
            return '';
        }
        
        return Storage::url($value);
    }
    
    public function attachmentable()
    {
        return $this->morphTo(__FUNCTION__, 'type', 'type_id');
    }
    
    public static function path($id)
    {
        $path = self::where('id', $id)->value('path');
        if (empty($path)) {
            return '';
        }
        
        return Storage::url($path);
    }

}

