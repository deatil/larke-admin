<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Support\Uuid;
use Larke\Admin\Model\Config as ConfigModel;

class Config
{
    public function creating(ConfigModel $model)
    {
        $model->id = Uuid::toString();
        
        $model->update_time = time();
        $model->update_ip = request()->ip();
        $model->create_time = time();
        $model->create_ip = request()->ip();
    }

    public function updating(ConfigModel $model)
    {
        $model->update_time = time();
        $model->update_ip = request()->ip();
    }
    
    /**
     * 插入到数据库
     */
    public function created(ConfigModel $model)
    {
        $model->clearCahce();
    }

    /**
     * 更新到数据库
     */
    public function updated(ConfigModel $model)
    {
        $model->clearCahce();
    }

    /**
     * 保存到数据库
     */
    public function saved(ConfigModel $model)
    {
        $model->clearCahce();
    }

    /**
     * 删除
     */
    public function deleted(ConfigModel $model)
    {
        $model->clearCahce();
    }
}
