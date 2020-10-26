<?php

namespace Larke\Admin\Observer;

use Larke\Admin\Model\AdminLog as AdminLogModel;

class AdminLog
{
    /**
     * 获取模型实例后
     */
    public function retrieved(AdminLogModel $log)
    {
    }

    /**
     * 插入到数据库
     */
    public function creating(AdminLogModel $log)
    {
        $log->id = md5(mt_rand(100000, 999999).microtime());
    }

    /**
     * 插入到数据库
     */
    public function created(AdminLogModel $log)
    {
    }

    /**
     * 更新到数据库
     */
    public function updating(AdminLogModel $log)
    {
    }

    /**
     * 更新到数据库
     */
    public function updated(AdminLogModel $log)
    {
    }

    /**
     * 保存到数据库
     */
    public function saving(AdminLogModel $log)
    {
    }

    /**
     * 保存到数据库
     */
    public function saved(AdminLogModel $log)
    {
    }

    /**
     * 删除
     */
    public function deleting(AdminLogModel $log)
    {
    }

    /**
     * 删除
     */
    public function deleted(AdminLogModel $log)
    {
    }

    /**
     * 恢复软删除前
     */
    public function restoring(AdminLogModel $log)
    {
    }
    
    /**
     * 恢复软删除后
     */
    public function restored(AdminLogModel $log)
    {
    }
}
