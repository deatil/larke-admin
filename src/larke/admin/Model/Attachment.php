<?php

namespace Larke\Admin\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 附件模型
 *
 * @create 2020-10-19
 * @author deatil
 */
class Attachment extends Model
{
    protected $table = 'larke_attachment';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    public $incrementing = false;
    public $timestamps = false;
    
    public static function path($id)
    {
        return self::where('id', $id)->value('path');
    }
    
    public function attachmentable()
    {
        return $this->morphTo(__FUNCTION__, 'type', 'type_id');
    }

}

