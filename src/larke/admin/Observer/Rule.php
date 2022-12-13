<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Support\Uuid;
use Larke\Auth\Models\Rule as RuleModel;

class Rule
{
    public function creating(RuleModel $model)
    {
        $model->id = Uuid::toString();
    }
    
    public function saved(RuleModel $rule)
    {
        $rule->refreshCache();
    }

    public function deleted(RuleModel $rule)
    {
        $rule->refreshCache();
    }
}
