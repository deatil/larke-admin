<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Model\Attachment as AttachmentModel;

class Attachment
{
    public function creating(AttachmentModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime().uniqid());
        
        $model->update_time = time();
        $model->update_ip = request()->ip();
        $model->create_time = time();
        $model->create_ip = request()->ip();
    }

    public function updating(AttachmentModel $model)
    {
        $model->update_time = time();
        $model->update_ip = request()->ip();
    }
}
