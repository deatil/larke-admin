<?php

namespace Larke\Admin;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Facades\Response;

use Larke\Admin\Contracts\Response as ResponseContract;
use Larke\Admin\Contracts\Jwt as JwtContract;
use Larke\Admin\Jwt\Jwt;
use Larke\Admin\Http\Response as HttpResponse;
use Larke\Admin\Http\ResponseCode;
use Larke\Admin\Service\Cache;
use Larke\Admin\Service\Loader;
use Larke\Admin\Auth\Admin;

// use directory
use Larke\Admin\Model;
use Larke\Admin\Observer;
use Larke\Admin\Command;
use Larke\Admin\Provider;
use Larke\Admin\Middleware;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * The application's alias.
     *
     * @var array
     */
    protected $alias = [
        'ResponseCode' => ResponseCode::class,
    ];
    
    /**
     * The application's commands.
     *
     * @var array
     */
    protected $commands = [
        Command\Install::class,
        Command\ImportRoute::class,
        Command\ResetPasword::class,
        Command\PassportLogout::class,
        Command\ResetEnforcer::class,
        Command\ClearCache::class,
        Command\Extension::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'larke.admin.auth' => Middleware\Authenticate::class,
        'larke.admin.auth.admin' => Middleware\AdminCheck::class,
        'larke.admin.permission' => Middleware\Permission::class,
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
            'larke.admin.permission',
            'larke.admin.log',
        ],
    ];
    
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerConfig();
        
        $this->registerAlias();
        
        $this->registerBind();
        
        $this->registerPublishing();
        
        $this->registerRouteMiddleware();
        
        $this->commands($this->commands);
        
        $this->registerEvent();
        
        $this->registerExtension();
    }
    
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->ensureHttps();
        
        $this->bootObserver();
        
        $this->bootExtension();
    }

    /**
     * Force to set https scheme if https enabled.
     *
     * @return void
     */
    protected function ensureHttps()
    {
        if (config('larkeadmin.https') || config('larkeadmin.secure')) {
            url()->forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    }
    
    /**
     * Register the ResponseCode.
     *
     * @return void
     */
    protected function registerAlias()
    {
        foreach ($this->alias as $alias => $class) {
            class_alias($class, $alias);
        }
    }
    
    /**
     * Register the config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../resource/config/larkeadmin.php', 'larkeadmin');
        
        $this->loadRoutesFrom(__DIR__ . '/../resource/routes/admin.php');
    }
    
    /**
     * Register the bind.
     *
     * @return void
     */
    protected function registerBind()
    {
        // 导入器
        $this->app->bind('larke.admin.loader', Loader::class);
        
        // json响应
        $this->app->bind('larke.admin.json', ResponseContract::class);
        $this->app->bind(ResponseContract::class, function() {
            $HttpResponse = new HttpResponse();
            
            $config = config('larkeadmin.response.json');
            $HttpResponse->withIsAllowOrigin($config['is_allow_origin'])
                ->withAllowOrigin($config['allow_origin'])
                ->withAllowCredentials($config['allow_credentials'])
                ->withMaxAge($config['max_age'])
                ->withAllowMethods($config['allow_methods'])
                ->withAllowHeaders($config['allow_headers'])
                ->withExposeHeaders($config['expose_headers']);
            
            return $HttpResponse;
        });
        
        // 系统使用缓存
        $this->app->singleton('larke.admin.cache', function() {
            $Cache = new Cache();
            return $Cache->store();
        });
        
        // 管理员登陆信息
        $this->app->singleton('larke.admin.admin', Admin::class);
        
        // jwt
        $this->app->bind('larke.admin.jwt', JwtContract::class);
        $this->app->bind(JwtContract::class, function() {
            $Jwt = new Jwt();
            $config = config('larkeadmin.jwt');

            $Jwt->withIss($config['iss']);
            $Jwt->withAud($config['aud']);
            $Jwt->withSub($config['sub']);
            
            $Jwt->withJti($config['jti']); // JWT ID
            $Jwt->withExp($config['exp']);
            $Jwt->withNbf($config['nbf']);
            $Jwt->withLeeway($config['leeway']);
            
            $Jwt->withSignerConfig($config['signer']);
            
            return $Jwt;
        });
        
        // 扩展
        $this->app->singleton('larke.admin.extension', Extension::class);
        
        // response()->success('获取成功');
        Response::macro('success', function($message = '获取成功', $data = null, $code = 0, $header = []) {
            return app('larke.admin.json')->json(true, $code, $message, $data, $header);
        });
        
        // response()->error('获取失败');
        Response::macro('error', function($message = '获取失败', $code = 1, $data = [], $header = []) {
            return app('larke.admin.json')->json(false, $code, $message, $data, $header);
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
                __DIR__.'/../resource/config' => config_path(),
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
    
    /**
     * Register Extensions.
     */
    public function registerExtension()
    {
        app('larke.admin.extension')->registerExtensionNamespace();
    }
    
    /**
     * Register Events.
     */
    public function registerEvent()
    {
        $this->app->register(Provider\EventServiceProvider::class);
    }

    /**
     * Boot Observer.
     *
     * @return void
     */
    protected function bootObserver()
    {
        Model\Admin::observe(new Observer\Admin());
        Model\AdminLog::observe(new Observer\AdminLog());
        Model\Attachment::observe(new Observer\Attachment());
        Model\AuthGroup::observe(new Observer\AuthGroup());
        Model\AuthGroupAccess::observe(new Observer\AuthGroupAccess());
        Model\AuthRule::observe(new Observer\AuthRule());
        Model\AuthRuleAccess::observe(new Observer\AuthRuleAccess());
        Model\Config::observe(new Observer\Config());
        Model\Extension::observe(new Observer\Extension());
    }

    /**
     * Boot Extension.
     *
     * @return void
     */
    protected function bootExtension()
    {
        app('larke.admin.extension')->bootExtension();
    }
    
}
