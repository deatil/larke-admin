<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Model\Admin as AdminModel;

class Admin
{
    public function creating(AdminModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime());
        
        $model->last_active = time();
        $model->last_ip = request()->ip();
        
        $model->create_time = time();
        $model->create_ip = request()->ip();
    }
}
