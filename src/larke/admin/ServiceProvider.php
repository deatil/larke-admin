<?php

namespace Larke\Admin;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Larke\Admin\Command\Install;
use Larke\Admin\Contracts\Response as ResponseContract;
use Larke\Admin\Contracts\Jwt as JwtContract;
use Larke\Admin\Jwt\Jwt;
use Larke\Admin\Http\Response as ResponseHttp;
use Larke\Admin\Service\Cache as CacheService;
use Larke\Admin\Auth\Admin as AdminData;
use Larke\Admin\Model\AdminLog as AdminLogModel;
use Larke\Admin\Model\Attachment as AttachmentModel;
use Larke\Admin\Observer\AdminLog as AdminLogObserver;
use Larke\Admin\Observer\Attachment as AttachmentObserver;

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
        'larke.admin.auth' => Middleware\Authenticate::class,
        'larke.admin.log' => Middleware\Log::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'larke.admin' => [
            'larke.admin.auth',
            'larke.admin.log',
        ],
    ];
    
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->ensureHttps();
        
        $this->registerConfig();
        
        $this->loadViewsFrom(__DIR__ . '/../resource/views', 'larke-admin');
        
        $this->loadRoutesFrom(__DIR__ . '/../resource/routes/admin.php');

        $this->registerBind();
        
        $this->registerPublishing();

        $this->bootObserver();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerRouteMiddleware();
        
        $this->commands($this->commands);
    }

    /**
     * Force to set https scheme if https enabled.
     *
     * @return void
     */
    protected function ensureHttps()
    {
        if (config('admin.https') || config('admin.secure')) {
            url()->forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    /**
     * Boot Observer.
     *
     * @return void
     */
    protected function bootObserver()
    {
        AdminLogModel::observe(new AdminLogObserver());
        
        AttachmentModel::observe(new AttachmentObserver());
    }
    
    /**
     * Register the config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../resource/config/larke.php', 'larke');
    }
    
    /**
     * Register the bind.
     *
     * @return void
     */
    protected function registerBind()
    {
        // json响应
        $this->app->bind('larke.json', ResponseContract::class);
        $this->app->bind(ResponseContract::class, ResponseHttp::class);
        
        // 系统使用缓存
        $this->app->singleton('larke.cache', function() {
            $CacheService = new CacheService();
            return $CacheService->store();
        });
        
        // 管理员登陆信息
        $this->app->singleton('larke.admin', AdminData::class);
        
        // jwt
        $this->app->bind('larke.jwt', JwtContract::class);
        $this->app->bind(JwtContract::class, function() {
            $Jwt = new Jwt();
            $config = config('larke.jwt');

            $Jwt->withAlg($config['alg']);
            $Jwt->withIss($config['iss']);
            $Jwt->withAud($config['aud']);
            $Jwt->withSub($config['sub']);
            
            $Jwt->withJti($config['jti']); // JWT ID
            $Jwt->withExpTime(intval($config['exptime']));
            $Jwt->withNotBeforeTime($config['notbeforetime']);
            
            $Jwt->withSignerType($config['signer_type']);
            $Jwt->withSecrect($config['secrect']);
            $Jwt->withPrivateKey($config['private_key']);
            $Jwt->withPublicKey($config['public_key']);
            
            return $Jwt;
        });
    }
    
    /**
     * Register the publishing.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resource/config/larke.php' => config_path('larke.php'),
            ], 'larke-admin-config');
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
