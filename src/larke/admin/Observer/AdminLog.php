<?php

declare (strict_types = 1);

namespace Larke\Admin\Observer;

use Larke\Admin\Model\AdminLog as AdminLogModel;

class AdminLog
{
    /**
     * 获取模型实例后
     */
    public function retrieved(AdminLogModel $model)
    {
    }

    /**
     * 插入到数据库前
     */
    public function creating(AdminLogModel $model)
    {
        $model->id = md5(mt_rand(100000, 999999).microtime());
        
        $model->create_time = time();
        $model->create_ip = request()->ip();
    }

    /**
     * 插入到数据库后
     */
    public function created(AdminLogModel $model)
    {
    }

    /**
     * 更新到数据库
     */
    public function updating(AdminLogModel $model)
    {
    }

    /**
     * 更新到数据库
     */
    public function updated(AdminLogModel $model)
    {
    }

    /**
     * 保存到数据库
     */
    public function saving(AdminLogModel $model)
    {
    }

    /**
     * 保存到数据库
     */
    public function saved(AdminLogModel $model)
    {
    }

    /**
     * 删除
     */
    public function deleting(AdminLogModel $model)
    {
    }

    /**
     * 删除
     */
    public function deleted(AdminLogModel $model)
    {
    }

    /**
     * 恢复软删除前
     */
    public function restoring(AdminLogModel $model)
    {
    }
    
    /**
     * 恢复软删除后
     */
    public function restored(AdminLogModel $model)
    {
    }
}
