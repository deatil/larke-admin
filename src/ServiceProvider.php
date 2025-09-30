<?php

declare (strict_types = 1);

namespace Larke\Admin;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Larke\Admin\Jwt\JWT;
use Larke\Admin\Jwt\Driver\Larke as LarkeJWT;
use Larke\Admin\Jwt\Contracts\JWT as JWTContract;
use Larke\Admin\Service\Cache;
use Larke\Admin\Support\Crypt;
use Larke\Admin\Support\Loader;
use Larke\Admin\Captcha\Captcha;
use Larke\Admin\Extension\Manager as ExtensionManager;
use Larke\Admin\Permission\Manager as PermissionManager;

// 文件夹引用
use Larke\Admin\Auth;
use Larke\Admin\Http;
use Larke\Admin\Model;
use Larke\Admin\Events;
use Larke\Admin\Command;
use Larke\Admin\Observer;
use Larke\Admin\Provider;
use Larke\Admin\Contracts;
use Larke\Admin\Middleware;

// 使用方法
use function app;
use function url;
use function config;

/**
 * 服务提供者
 *
 * @create 2020-10-30
 * @author deatil
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * 别名
     *
     * @var array
     */
    protected $alias = [
        'ResponseCode' => Http\ResponseCode::class,
    ];
    
    /**
     * 脚本
     *
     * @var array
     */
    protected $commands = [
        Command\Install::class,
        Command\AppAdmin::class,
        Command\ImportMenus::class,
        Command\ImportRoute::class,
        Command\ResetPassword::class,
        Command\PassportLogout::class,
        Command\ResetPermission::class,
        Command\ClearCache::class,
        Command\Extension::class,
        Command\JWTGenerateSecret::class,
    ];

    /**
     * 路由中间件
     *
     * @var array
     */
    protected $routeMiddleware = [
        'larke-admin.lang'       => Middleware\CheckLang::class,
        'larke-admin.auth'       => Middleware\Authenticate::class,
        'larke-admin.login-type' => Middleware\CheckLoginType::class,
        'larke-admin.admin-auth' => Middleware\AdminCheck::class,
        'larke-admin.permission' => Middleware\Permission::class,
    ];

    /**
     * 中间件分组
     *
     * @var array
     */
    protected $middlewareGroups = [
        'larke-admin' => [
            'larke-admin.lang',
            'larke-admin.auth',
            'larke-admin.login-type',
            'larke-admin.permission',
        ],
    ];

    /**
     * 服务提供者
     *
     * @var array
     */
    protected $providers = [
        Provider\EventServiceProvider::class,
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
        
        $this->registerProviders();
        
        $this->registerGlobalMiddleware();
        
        $this->ensureHttps();
    }
    
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->bootBind();
        
        $this->bootObserver();
        
        $this->bootRelationMorphMap();
        
        $this->bootExtension();
        
        $this->bootCommand();

        $this->bootBladeDirectives();
        
        // 运行前
        app('larke-admin.extension')->callBooting();
        
        // 运行后
        app('larke-admin.extension')->callBooted();
    }

    /**
     * 开启https
     *
     * @return void
     */
    protected function ensureHttps()
    {
        // 是否为 admin 路径
        $iaAdminPath = Str::startsWith(request()->decodedPath(), '/' . ltrim(config('larkeadmin.route.prefix'), '/'));
        
        if (
            (config('larkeadmin.https') || config('larkeadmin.secure'))
            && $iaAdminPath
        ) {
            url()->forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    }
    
    /**
     * 别名
     *
     * @return void
     */
    protected function registerAlias()
    {
        $aliasLoader = AliasLoader::getInstance();
        foreach ($this->alias as $alias => $class) {
            $aliasLoader->alias($alias, $class);
        }
    }
    
    /**
     * 配置
     *
     * @return void
     */
    protected function registerConfig()
    {
        // 配置
        $this->mergeConfigFrom(__DIR__ . '/../resources/config/larkeadmin.php', 'larkeadmin');
        
        // 语言包
        $langPath = __DIR__ . '/../resources/lang';
        $this->loadTranslationsFrom($langPath, 'larke-admin');
        $this->loadJsonTranslationsFrom($langPath);
        
        // 路由
        $this->loadRoutesFrom(__DIR__ . '/../resources/routes/admin.php');
        
        // 动态设置版本号
        config(Arr::dot([
            'version' => Admin::VERSION,
            'release' => Admin::RELEASE,
        ], 'larkeadmin.admin.'));
    }
    
    /**
     * 绑定
     *
     * @return void
     */
    protected function registerBind()
    {
        // 加载器
        $this->app->bind('larke-admin.loader', Loader::class);

        // 事件
        $this->app->singleton('larke-admin.events', function() {
            $pool = new Events\Pool();
            return new Events\Events($pool);
        });
        
        // 验证码
        $this->app->bind('larke-admin.captcha', Contracts\Captcha::class);
        $this->app->bind(Contracts\Captcha::class, function() {
            $captcha = new Captcha();
            
            $config = config('larkeadmin.captcha');
            $config = collect($config)
                ->filter(function($data) {
                    return !empty($data);
                })
                ->toArray();
            
            // 字体没设置重设
            if (empty($config['font'])) {
                $config['font'] = __DIR__ . '/../resources/font/icon.ttf';
            }
            
            $captcha->withConfig($config);
            
            return $captcha;
        });
        
        // 响应
        $this->app->bind('larke-admin.response', Contracts\Response::class);
        $this->app->bind(Contracts\Response::class, function() {
            $httpResponse = new Http\Response();
            
            $config = config('larkeadmin.response');
            
            if ($config['is_allow_origin']) {
                $httpResponse->withAllowOrigin($config['allow_origin'])
                    ->withAllowCredentials($config['allow_credentials'])
                    ->withMaxAge($config['max_age'])
                    ->withAllowMethods($config['allow_methods'])
                    ->withAllowHeaders($config['allow_headers'])
                    ->withExposeHeaders($config['expose_headers']);
            }
            
            return $httpResponse;
        });
        
        // 系统使用缓存
        $this->app->singleton('larke-admin.cache', function() {
            $cache = new Cache();
            return $cache->store();
        });
        
        // 加密数据
        $this->app->bind('larke-admin.crypto', Contracts\Crypt::class);
        $this->app->bind(Contracts\Crypt::class, function($app) {
            $crypt = new Crypt();
            $crypt->withMode(config('larkeadmin.crypto.mode'));
            $crypt->withIv(config('larkeadmin.crypto.iv'));
            return $crypt;
        });
        
        // Jwt 底层驱动
        $this->app->bind('larke-admin.jwt-driver', JWTContract::class);
        $this->app->bind(JWTContract::class, LarkeJWT::class);
        
        // jwt
        $this->app->bind('larke-admin.jwt', function($app) {
            $jwt = new JWT(
                $app['larke-admin.jwt-driver'],
                $app['larke-admin.crypto'],
                collect(config('larkeadmin.jwt'))
            );
            
            return $jwt;
        });
        
        // 扩展
        $this->app->singleton('larke-admin.extension', function() {
            $manager = new ExtensionManager(
                config('larkeadmin.extension.directory'), 
                config('larkeadmin.extension.cache_id'), 
                config('larkeadmin.extension.cache_time')
            );
            
            return $manager;
        });
        
        // 权限
        $this->app->singleton('larke-admin.permission', function() {
            $manager = new PermissionManager(
                config('larkeadmin.auth.enforcer_guard')
            );
            
            return $manager;
        });
        
        // 管理员登陆信息
        $this->app->singleton('larke-admin.auth-admin', Auth\Admin::class);
        
        // 权限token
        $this->app->singleton('larke-admin.auth-token', Auth\Token::class);
    }
    
    /**
     * 绑定
     *
     * @return void
     */
    protected function bootBind()
    {
        // response()->success('success');
        Response::macro('success', function($message = "", $data = "", $header = [], $code = 0) {
            return app('larke-admin.response')->json(true, $code, $message, $data, $header);
        });
        
        // response()->error('error');
        Response::macro('error', function($message = "", $code = 1, $data = [], $header = []) {
            return app('larke-admin.response')->json(false, $code, $message, $data, $header);
        });
    }
    
    /**
     * 推送
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/config/larkeadmin.php' => config_path('larkeadmin.php.larke'),
                __DIR__.'/../resources/config/larkeauth.php' => config_path('larkeauth.php.larke'),
                __DIR__.'/../resources/config/larkeauth-rbac-model.conf' => config_path('larkeauth-rbac-model.conf.larke'),
            ], 'larke-admin-config');
        }
    }
    
    /**
     * 中间件
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // 注册路由
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        // 注册路由分组
        foreach ($this->middlewareGroups as $key => $middlewares) {
            foreach ($middlewares as $middleware) {
                app('router')->pushMiddlewareToGroup($key, $middleware);
            }
        }
    }
    
    /**
     * 服务提供者
     *
     * @return void
     */
    public function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * 全局中间件
     *
     * @return void
     */
    protected function registerGlobalMiddleware()
    {
        // 错误返回json
        $this->app
            ->make(HttpKernel::class)
            ->prependMiddleware(Middleware\JsonExceptionHandler::class);
        
        // 跨域处理
        $this->app
            ->make(HttpKernel::class)
            ->pushMiddleware(Middleware\RequestOptions::class);
    }

    /**
     * 模型事件
     *
     * @return void
     */
    protected function bootObserver()
    {
        Model\Admin::observe(new Observer\Admin());
        Model\Attachment::observe(new Observer\Attachment());
        Model\AuthGroup::observe(new Observer\AuthGroup());
        Model\AuthRule::observe(new Observer\AuthRule());
        Model\Config::observe(new Observer\Config());
        Model\Extension::observe(new Observer\Extension());
    }

    /**
     * 自定义多态类型
     *
     * @return void
     */
    protected function bootRelationMorphMap()
    {
        Relation::morphMap([
            'admins' => 'Larke\\Admin\\Model\\Admin',
        ]);
    }

    /**
     * 加载扩展
     *
     * @return void
     */
    protected function bootExtension()
    {
        app('larke-admin.extension')->bootExtension();
    }

    /**
     * 脚本
     *
     * @return void
     */
    protected function bootCommand()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * 添加 blade hook 标签
     *
     * @hook('xxx') 
     * --- 
     * @endhook
     */
    protected function bootBladeDirectives()
    {
        Blade::directive('hook', function ($parameter) {
            $parameter  = trim($parameter, '()');
            $parameters = preg_split('/[,\r\n ]+/', trim($parameter, ",\r\n"));
            $name       = array_shift($parameters);
            
            return ' <?php
                    $__hook_name = ' . $name . ';
                    $__hook_vars = [' . implode(', ', $parameters) . '];
                    ob_start();
                ?>';
        });

        Blade::directive('endhook', function () {
            return ' <?php
                $__hook_content = ob_get_clean();
                echo \Larke\Admin\apply_filters($__hook_name, $__hook_content, ...$__hook_vars);
                unset($__hook_name, $__hook_vars, $__hook_content);
                ?>';
        });
    }
}
