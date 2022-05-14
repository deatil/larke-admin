<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Facade\Permission;
use Larke\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;

class AuthGroupAccess
{
    public function creating(AuthGroupAccessModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime().uniqid());
    }
    
    public function created(AuthGroupAccessModel $model)
    {
        Permission::addRoleForUser($model->admin_id, $model->group_id);
    }
    
    public function deleting(AuthGroupAccessModel $model)
    {
        Permission::deleteRoleForUser($model->admin_id, $model->group_id);
    }
}
