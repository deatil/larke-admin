<?php

namespace Larke\Admin\Service;

use Illuminate\Support\Facades\Cache as LaravelCache;

/**
 * Cache
 *
 * @create 2020-10-27
 * @author deatil
 */
class Cache
{
    /**
     * a cache store.
     *
     * @var \Illuminate\Cache\Repository
     */
    protected $store;
    
    /**
     * Cache store.
     */
    public function store($store = null)
    {
        if (!$store) {
            $store = config('larke.cache.store', 'default');
            $store = ('default' == $store) ? null : $store;
        }
        $this->store = LaravelCache::store($store);
        
        return $this->store;
    }
}
