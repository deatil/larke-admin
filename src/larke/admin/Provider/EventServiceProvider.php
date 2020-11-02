<?php

namespace Larke\Admin\Provider;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Larke\Admin\Listener\Config as ConfigListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];
    
    /**
     * The event subscribe mappings for the application.
     *
     * @var array
     */
    protected $subscribe = [
        ConfigListener::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
