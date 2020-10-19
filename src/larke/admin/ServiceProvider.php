<?php

namespace Larke\Admin;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Larke\Admin\Command\Install;
use Larke\Admin\Contracts\Response as ResponseContract;
use Larke\Admin\Http\Response as ResponseHttp;

class ServiceProvider extends BaseServiceProvider
{
    protected $commands = [
        Install::class,
    ];

    public function register()
    {
        $this->commands($this->commands);
    }
    
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resource/views', 'larke-admin');
        
        $this->loadRoutesFrom(__DIR__ . '/../resource/routes/admin.php');

        $this->registerBind();
        
        $this->registerPublishing();
    }
    
    protected function registerBind()
    {
        // json响应
        $this->app->bind('larke.json', ResponseContract::class);
        $this->app->bind(ResponseContract::class, ResponseHttp::class);
    }
    
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config' => config_path()
            ], 'larke-admin-config');
            
            if ($this->app->runningInConsole()) {
                $this->publishes([
                    __DIR__.'/../resource/assets' => public_path('larke-admin'),
                ], 'larke-admin-assets');
            }
        }
    }
}
