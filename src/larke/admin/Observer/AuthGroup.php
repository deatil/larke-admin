<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Support\Uuid;
use Larke\Admin\Model\AuthGroup as AuthGroupModel;

class AuthGroup
{
    public function creating(AuthGroupModel $model)
    {
        $model->id = Uuid::toString();
        
        $model->update_time = time();
        $model->update_ip = request()->ip();
        $model->create_time = time();
        $model->create_ip = request()->ip();
    }

    public function updating(AuthGroupModel $model)
    {
        $model->update_time = time();
        $model->update_ip = request()->ip();
    }
    
    public function updated(AuthGroupModel $model)
    {
        if ($model->status != 1) {
            $model->groupAccess()
                ->where('group_id', $model->id)
                ->get()
                ->each
                ->delete();
        }
    }
    
    public function deleted(AuthGroupModel $model)
    {
        $model->groupAccess()
            ->where('group_id', $model->id)
            ->get()
            ->each
            ->delete();
    }
}
