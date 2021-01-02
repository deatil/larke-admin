<?php

declare (strict_types = 1);

namespace Larke\Admin\Listener;

use Larke\Admin\Event\ConfigCreated as ConfigCreatedEvent;
use Larke\Admin\Event\ConfigUpdated as ConfigUpdatedEvent;

/*
 * 配置
 *
 * @create 2020-11-2
 * @author deatil
 */
class Config
{
    public function onCreate($event)
    {
        $value = $event->config->value;
    }
    
    public function onUpdate($event)
    {
        $value = $event->config->value;
    }
    
    public function subscribe($events)
    {
        $events->listen(
            ConfigCreatedEvent::class,
            Config::class . '@onCreate'
        );
        
        $events->listen(
            ConfigUpdatedEvent::class,
            Config::class . '@onUpdate'
        );
    }
    
}
