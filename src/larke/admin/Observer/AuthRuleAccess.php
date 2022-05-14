<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Facade\Permission;
use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;

class AuthRuleAccess
{
    public function creating(AuthRuleAccessModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime().uniqid());
    }
    
    public function created(AuthRuleAccessModel $model)
    {
        $rule = $model->rule;
        if (!empty($rule)) {
            Permission::addPolicy($model->group_id, $rule['slug'], strtoupper($rule['method']));
        }
    }
    
    public function deleting(AuthRuleAccessModel $model)
    {
        $rule = $model->rule;
        if (!empty($rule)) {
            Permission::deletePolicy($model->group_id, $rule['slug'], strtoupper($rule['method']));
        }
    }
}
