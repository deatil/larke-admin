<?php

namespace Larke\Admin\Observer;

use Larke\Admin\Model\AuthRule as AuthRuleModel;

class AuthRule
{
    public function creating(AuthRuleModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime());
        
        $model->update_time = time();
        $model->update_ip = request()->ip();
        $model->create_time = time();
        $model->create_ip = request()->ip();
    }

    public function updating(AuthRuleModel $model)
    {
        $model->update_time = time();
        $model->update_ip = request()->ip();
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
