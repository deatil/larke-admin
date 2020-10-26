<?php

namespace Larke\Admin\Observer;

use Larke\Admin\Model\Attachment as AttachmentModel;

class Attachment
{
    /**
     * 插入到数据库
     */
    public function creating(AttachmentModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime());
    }
}
