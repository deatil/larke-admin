<?php

declare (strict_types = 1);

namespace Larke\Admin\Events;

/**
 * 过滤事件
 */
class Filter extends Event
{
    /**
     * 触发事件
     * 
     * @param string|object $event  事件名称
     * @param mixed         $params 需要过滤的数据
     * @param mixed         $var    更多参数
     * @return mixed
     */
    public function trigger($event, $params = null, ...$var)
    {
        if (is_object($event)) {
            $params = $event;
            $event  = $event::class;
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

        $tmp = $var;
        $result = $params;
        foreach ($this->range($listeners) as $listener) {
            array_unshift($tmp, $result);
            
            $result = $this->dispatch($listener['listener'], $tmp);
            $tmp = $var;
        }

        return $result;
    }
}
