<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;

class Admin
{
    /**
     * 创建前
     */
    public function creating(AdminModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime());
        
        $model->last_active = time();
        $model->last_ip = request()->ip();
        
        $model->create_time = time();
        $model->create_ip = request()->ip();
    }
    
    /**
     * 删除后
     */
    public function deleted(AdminModel $model)
    {
        AuthGroupAccessModel
            ::where('admin_id', $model->id)
            ->get()
            ->each
            ->delete();
    }

}
