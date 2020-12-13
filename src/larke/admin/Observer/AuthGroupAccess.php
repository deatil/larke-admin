<?php

namespace Larke\Admin\Observer;

use Larke\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;

class AuthGroupAccess
{
    public function creating(AuthGroupAccessModel $log)
    {
        $log->id = md5(mt_rand(100000, 999999).microtime());
    }
    
    public function created(AuthGroupAccessModel $model)
    {
        \Enforcer::addRoleForUser($model->admin_id, $model->group_id);
    }
    
    public function deleting(AuthGroupAccessModel $model)
    {
        \Enforcer::deleteRoleForUser($model->admin_id, $model->group_id);
    }
}
