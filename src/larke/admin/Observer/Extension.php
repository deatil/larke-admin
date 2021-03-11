<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Model\Extension as ExtensionModel;

class Extension
{
    /**
     * 插入到数据库
     */
    public function creating(ExtensionModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime());
        $model->installtime = time();
        $model->upgradetime = time();
        
        $model->update_time = time();
        $model->update_ip = request()->ip();
        $model->create_time = time();
        $model->create_ip = request()->ip();
    }
    
    /**
     * 更新到数据库
     */
    public function updating(ExtensionModel $model)
    {
        $model->update_time = time();
        $model->update_ip = request()->ip();
    }
    
    /**
     * 插入到数据库
     */
    public function created(ExtensionModel $model)
    {
        $model->clearCahce();
    }

    /**
     * 更新到数据库
     */
    public function updated(ExtensionModel $model)
    {
        $model->clearCahce();
    }

    /**
     * 保存到数据库
     */
    public function saved(ExtensionModel $model)
    {
        $model->clearCahce();
    }

    /**
     * 删除
     */
    public function deleted(ExtensionModel $model)
    {
        $model->clearCahce();
    }
    
}
