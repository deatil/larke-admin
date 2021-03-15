<?php

declare (strict_types = 1);

namespace Larke\Admin;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Larke\Admin\Contracts\Response as ResponseContract;
use Larke\Admin\Contracts\Jwt as JwtContract;
use Larke\Admin\Contracts\Captcha as CaptchaContract;
use Larke\Admin\Jwt\Jwt;
use Larke\Admin\Http\Response as HttpResponse;
use Larke\Admin\Http\ResponseCode;
use Larke\Admin\Service\Cache;
use Larke\Admin\Support\Loader;
use Larke\Admin\Auth\Admin as AuthAdmin;
use Larke\Admin\Auth\Token as AuthToken;
use Larke\Admin\Captcha\Captcha;

// 文件夹引用
use Larke\Admin\Model;
use Larke\Admin\Observer;
use Larke\Admin\Command;
use Larke\Admin\Provider;
use Larke\Admin\Middleware;

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
        'ResponseCode' => ResponseCode::class,
    ];
    
    /**
     * 脚本
     *
     * @var array
     */
    protected $commands = [
        Command\Install::class,
        Command\ImportRoute::class,
        Command\ResetPasword::class,
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
        'larke-admin.lang' => Middleware\CheckLang::class,
        'larke-admin.auth' => Middleware\Authenticate::class,
        'larke-admin.admin-auth' => Middleware\AdminCheck::class,
        'larke-admin.permission' => Middleware\Permission::class,
        'larke-admin.log' => Middleware\Log::class,
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
            'larke-admin.permission',
            'larke-admin.log',
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
        
        $this->commands($this->commands);
        
        $this->registerProviders();
    }
    
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->ensureHttps();
        
        $this->bootGlobalMiddleware();
        
        $this->bootObserver();
        
        $this->bootExtension();
    }

    /**
     * 开启https
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
     * 别名
     *
     * @return void
     */
    protected function registerAlias()
    {
        foreach ($this->alias as $alias => $class) {
            AliasLoader::getInstance()->alias($alias, $class);
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
        
        // 验证码
        $this->app->bind('larke-admin.captcha', CaptchaContract::class);
        $this->app->bind(CaptchaContract::class, function() {
            $captcha = new Captcha();
            
            $config = config('larkeadmin.captcha');
            $config = collect($config)
                ->filter(function($data) {
                    return !empty($data);
                })
                ->toArray();
            $captcha->withConfig($config);
            
            return $captcha;
        });
        
        // 响应
        $this->app->bind('larke-admin.response', ResponseContract::class);
        $this->app->bind(ResponseContract::class, function() {
            $httpResponse = new HttpResponse();
            
            $config = config('larkeadmin.response.json');
            $httpResponse
                ->withIsAllowOrigin($config['is_allow_origin'])
                ->withAllowOrigin($config['allow_origin'])
                ->withAllowCredentials($config['allow_credentials'])
                ->withMaxAge($config['max_age'])
                ->withAllowMethods($config['allow_methods'])
                ->withAllowHeaders($config['allow_headers'])
                ->withExposeHeaders($config['expose_headers']);
            
            return $httpResponse;
        });
        
        // 系统使用缓存
        $this->app->singleton('larke-admin.cache', function() {
            $cache = new Cache();
            return $cache->store();
        });
        
        // jwt
        $this->app->bind('larke-admin.jwt', JwtContract::class);
        $this->app->bind(JwtContract::class, function() {
            $jwt = new Jwt();
            $config = config('larkeadmin.jwt');

            $jwt->withIss($config['iss']);
            $jwt->withAud($config['aud']);
            $jwt->withSub($config['sub']);
            
            $jwt->withJti($config['jti']); // JWT ID
            $jwt->withExp($config['exp']);
            $jwt->withNbf($config['nbf']);
            $jwt->withLeeway($config['leeway']);
            
            $jwt->withPassphrase($config['passphrase']);
            
            $jwt->withSignerConfig($config['signer']);
            
            return $jwt;
        });
        
        // 扩展
        $this->app->singleton('larke-admin.extension', Extension::class);
        
        // 管理员登陆信息
        $this->app->singleton('larke-admin.auth-admin', AuthAdmin::class);
        
        // 权限token
        $this->app->singleton('larke-admin.auth-token', AuthToken::class);
        
        // response()->success('success');
        Response::macro('success', function($message = null, $data = null, $header = [], $code = 0) {
            return app('larke-admin.response')->json(true, $code, $message, $data, $header);
        });
        
        // response()->error('error');
        Response::macro('error', function($message = null, $code = 1, $data = [], $header = []) {
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
                __DIR__.'/../resources/config' => config_path(),
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
    protected function bootGlobalMiddleware()
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
     * 加载扩展
     *
     * @return void
     */
    protected function bootExtension()
    {
        app('larke-admin.extension')->bootExtension();
    }
    
}
