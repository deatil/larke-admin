<?php

declare (strict_types = 1);

namespace Larke\Admin\Events;

/**
 * 事件管理类
 */
class Events
{
    /**
     * 动作事件
     */
    protected Action $action;

    /**
     * 过滤事件
     */
    protected Filter $filter;
    
    public function __construct($pool)
    {
        $this->action = new Action($pool);
        $this->filter = new Filter($pool);
    }

    /**
     * 获取动作事件
     */
    public function getAction(): Action
    {
        return $this->action;
    }

    /**
     * 获取过滤事件
     */
    public function getFilter(): Filter
    {
        return $this->filter;
    }

}
