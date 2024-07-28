<?php

declare (strict_types = 1);

namespace Larke\Admin\Events;

use ReflectionClass;
use ReflectionMethod;

/**
 * 事件管理类
 */
abstract class Event
{
    /**
     * 监听者
     * @var array
     */
    protected $listener = [];

    /**
     * 应用池
     */
    protected $pool;

    public function __construct($pool)
    {
        $this->pool = $pool;
    }

    /**
     * 注册事件订阅者
     * 
     * @param mixed $subscriber 订阅者
     * @return $this
     */
    public function subscribe($subscriber)
    {
        $subscribers = (array) $subscriber;

        foreach ($subscribers as $subscriber) {
            if (is_string($subscriber)) {
                $subscriber = $this->pool->make($subscriber);
            }

            if (method_exists($subscriber, 'subscribe')) {
                // 手动订阅
                $subscriber->subscribe($this);
            } else {
                // 自动订阅
                $this->observe($subscriber);
            }
        }

        return $this;
    }

    /**
     * 自动注册事件观察者
     * 
     * @param string|object $observer 观察者
     * @param null|string   $prefix   事件名前缀
     * @param bool          $sort     排序
     * @return $this
     */
    public function observe($observer, string $prefix = '', int $sort = 1)
    {
        if (is_string($observer)) {
            $observer = $this->pool->make($observer);
        }

        $reflect = new ReflectionClass($observer);
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);

        if ($reflect->hasProperty('eventPrefix')) {
            $propertyPrefix = $reflect->getProperty('eventPrefix');
            $propertyPrefix->setAccessible(true);
            $prefix = $propertyPrefix->getValue($observer);
        }

        if ($reflect->hasProperty('eventSort')) {
            $propertySort = $reflect->getProperty('eventSort');
            $propertySort->setAccessible(true);
            $sort = $propertySort->getValue($observer);
        }

        foreach ($methods as $method) {
            $name = $method->getName();
            if (str_starts_with($name, 'on')) {
                $this->listen($prefix . substr($name, 2), [$observer, $name], $sort);
            }
        }

        return $this;
    }

    /**
     * 注册事件监听
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @param bool   $sort     排序
     * @return $this
     */
    public function listen(string $event, $listener, int $sort = 1)
    {
        if (! isset($this->listener[$event])) {
            $this->listener[$event] = [];
        }
        
        $this->listener[$event][] = [
            'listener' => $listener,
            'sort'     => $sort,
            'key'      => $this->filterBuildUniqueId($listener),
        ];

        return $this;
    }

    /**
     * 移除监听事件
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @param bool   $sort     排序
     * @return bool
     */
    public function removeListener(string $event, $listener, int $sort = 1): bool
    {
        $key = $this->filterBuildUniqueId($listener);

        $exists = isset($this->listener[$event]);
        if ($exists) {
            foreach ($this->listener[$event] as $k => $v) {
                if ($v['key'] == $key && $v['sort'] == $sort) {
                    unset($this->listener[$event][$k]);
                }
            }
        }

        return $exists;
    }
    
    /**
     * 事件是否在监听
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @return bool
     */
    public function hasListener(string $event, $listener = false): bool
    {
        if (false === $listener) {
            return $this->hasListeners();
        }

        if (! isset($this->listener[$event])) {
            return false;
        }

        $key = $this->filterBuildUniqueId($listener);
        if (! $key) {
            return false;
        }
        
        foreach ($this->listener[$event] as $listen) {
            if ($listen['key'] == $key) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * 是否有事件监听
     * 
     * @return bool
     */
    public function hasListeners(): bool 
    {
        foreach ($this->listener as $listener) {
            if ($listener) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * 获取所有事件监听
     * 
     * @return array
     */
    public function getListeners()
    {
        return $this->listener;
    }
    
    /**
     * 是否存在事件监听点
     * 
     * @param string $event 事件名称
     * @return bool
     */
    public function exists(string $event): bool
    {
        return isset($this->listener[$event]);
    }

    /**
     * 移除事件监听点
     * 
     * @param string $event 事件名称
     * @return void
     */
    public function remove(string $event): void
    {
        unset($this->listener[$event]);
    }

    /**
     * 清空
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->listener = [];
    }
    
    /**
     * 执行事件调度
     * 
     * @param mixed $event  事件方法
     * @param mixed $params 参数
     * @return mixed
     */
    protected function dispatch($event, array $params = [])
    {
        if (! is_string($event)) {
            $call = $event;
        } elseif (str_contains($event, '::')) {
            $call = $event;
        } elseif (function_exists($event)) {
            $call = $event;
        } else {
            $obj  = $this->pool->make($event);
            $call = [$obj, 'handle'];
        }

        return $this->pool->call($call, $params);
    }
    
    /**
     * 排序
     */
    protected function arraySort(array $arr, string $key, string $type = 'desc')
    {
        $keyValue = [];
        foreach ($arr as $k => $v) {
            $keyValue[$k] = $v[$key];
        }
        
        if (strtolower($type) == 'asc') {
            asort($keyValue);
        } else {
            arsort($keyValue);
        }
        
        reset($keyValue);
        
        $newArray = [];
        foreach ($keyValue as $k => $v) {
            $newArray[$k] = $arr[$k];
        }
        
        return $newArray;
    }
    
    /**
     * 生成唯一值
     */
    protected function filterBuildUniqueId($callback) 
    {
        if (is_string($callback)) {
            return $callback;
        }

        if (is_object($callback)) {
            $callback = array($callback, '');
        } else {
            $callback = (array) $callback;
        }

        if (is_object($callback[0])) {
            return spl_object_hash($callback[0]) . $callback[1];
        } elseif (is_string($callback[0])) {
            return $callback[0] . '::' . $callback[1];
        }
    }
}
