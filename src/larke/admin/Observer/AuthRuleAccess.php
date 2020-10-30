<?php

namespace Larke\Admin\Observer;

use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;

class AuthRuleAccess
{
    public function creating(AuthRuleAccessModel $log)
    {
        $log->id = md5(mt_rand(100000, 999999).microtime());
    }
    
    public function created(AuthRuleAccessModel $model)
    {
        $rule = $model->rule;
        if (!empty($rule)) {
            \Enforcer::addPolicy($model->group_id, $rule['slug'], strtoupper($rule['method']));
        }
    }
    
    public function deleted(AuthRuleAccessModel $model)
    {
        $rule = $model->rule;
        if (!empty($rule)) {
            \Enforcer::deletePermissionForUser($model->group_id, $rule['slug'], strtoupper($rule['method']));
        }
    }
}
