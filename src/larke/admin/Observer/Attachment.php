<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Support\Uuid;
use Larke\Admin\Model\Attachment as AttachmentModel;

class Attachment
{
    public function creating(AttachmentModel $model)
    {
        $model->id = Uuid::toString();
        
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
