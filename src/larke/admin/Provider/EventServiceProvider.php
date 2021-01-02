<?php

declare (strict_types = 1);

namespace Larke\Admin\Provider;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

// use directory
use Larke\Admin\Event;
use Larke\Admin\Listener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Event\PassportLoginAfter::class => [
            Listener\PassportLoginAfter::class
        ],
    ];
    
    /**
     * The event subscribe mappings for the application.
     *
     * @var array
     */
    protected $subscribe = [
        Listener\Config::class,
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
