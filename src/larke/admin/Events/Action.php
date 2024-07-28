<?php

declare (strict_types = 1);

namespace Larke\Admin\Events;

/**
 * 操作事件
 */
class Action extends Event
{
    /**
     * 触发操作
     * 
     * @param string|object $event 事件名称
     * @param mixed         $var   更多参数
     * @return void
     */
    public function trigger($event, ...$var): void
    {
        if (is_object($event)) {
            $event = $event::class;
        }

        $listeners = $this->listener[$event] ?? [];

        if (str_contains($event, '.*')) {
            [$prefix, $event] = explode('.', $event, 2);

            foreach ($this->listener as $e => $listener) {
                if ($event == '*' && str_starts_with($e, $prefix . '.')) {
                    $listeners = array_merge($listeners, $listener);
                }
            }
        }

        $listeners = $this->arraySort($listeners, 'sort');

        foreach ($listeners as $key => $listener) {
            $this->dispatch($listener['listener'], $var);
        }
    }

}
