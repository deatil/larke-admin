<?php

namespace Larke\Admin\Observer;

use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;

class AuthRule
{
    public function creating(AuthRuleModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime());
    }
    
    public function updated(AuthRuleModel $model)
    {
        if ($model->status != 1) {
            $model->ruleAccess()
                ->where('rule_id', $model->id)
                ->get()
                ->each
                ->delete();
        }
    }
    
    public function deleting(AuthRuleModel $model)
    {
        $model->ruleAccess()
            ->where('rule_id', $model->id)
            ->get()
            ->each
            ->delete();
    }
}
