<?php

namespace Larke\Admin;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Larke\Admin\Command\Install;
use Larke\Admin\Contracts\Response as ResponseContract;
use Larke\Admin\Contracts\Jwt as JwtContract;
use Larke\Admin\Http\Response as ResponseHttp;
use Larke\Admin\Jwt\Jwt;

class ServiceProvider extends BaseServiceProvider
{
    protected $commands = [
        Install::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin.auth' => Middleware\Authenticate::class,
        'admin.log' => Middleware\Log::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'admin' => [
            'admin.auth',
            'admin.log',
        ],
    ];
    
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerConfig();
        
        $this->loadViewsFrom(__DIR__ . '/../resource/views', 'larke-admin');
        
        $this->loadRoutesFrom(__DIR__ . '/../resource/routes/admin.php');

        $this->registerBind();
        
        $this->registerPublishing();
    }

    public function register()
    {
        $this->registerRouteMiddleware();
        
        $this->commands($this->commands);
    }
    
    protected function registerConfig()
    {
        $configDir = __DIR__.'/../resource/config';
        
        $files = [];
        $files = array_merge($files, glob($configDir . '/*.php'));
        foreach ($files as $file) {
            config([
                pathinfo($file, PATHINFO_FILENAME) => include($file),
            ]);
        }
    }
    
    protected function registerBind()
    {
        // jwt
        $this->app->bind('larke.jwt', JwtContract::class);
        $this->app->bind(JwtContract::class, function() {
            $Jwt = new Jwt();
            $config = config('larke.jwt');

            $Jwt->withAlg($config['alg']);
            $Jwt->withIss($config['iss']);
            $Jwt->withAud($config['aud']);
            $Jwt->withSub($config['sub']);
            
            $Jwt->withJti($config['jti']); // device_id
            $Jwt->withExpTime(intval($config['exptime']));
            $Jwt->withNotBeforeTime($config['notbeforetime']);
            
            $Jwt->withSignerType($config['signer_type']);
            $Jwt->withSecrect($config['secrect']);
            $Jwt->withPrivateKey($config['private_key']);
            $Jwt->withPublicKey($config['public_key']);
            
            return $Jwt;
        });
        
        // json响应
        $this->app->bind('larke.json', ResponseContract::class);
        $this->app->bind(ResponseContract::class, ResponseHttp::class);
    }
    
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            if ($this->app->runningInConsole()) {
                $this->publishes([
                    __DIR__.'/../resource/assets/admin' => public_path('admin'),
                ], 'larke-admin-view');
            }
        }
    }
    
    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }
    
}
