<?php

declare (strict_types = 1);

namespace Larke\Admin;

use Illuminate\Support\Arr;

use Larke\Admin\Facade\Events;
use Larke\Admin\Facade\Extension;
use Larke\Admin\Facade\AuthAdmin;
use Larke\Admin\Service\Route as RouteService;
use Larke\Admin\Model\Config as ConfigModel;
use Larke\Admin\Model\Attachment as AttachmentModel;
use Larke\Admin\Model\Extension as ExtensionModel;
use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

if (! function_exists('Larke\\Admin\\success')) {
    /**
     * 返回成功JSON
     *
     * @param string $message 信息
     * @param array  $data    数据
     * @param array  $header  响应头
     * @param int    $code    状态码
     * @return mixed
     *
     * @create 2020-10-19
     * @author deatil
     */
    function success(?string $message = null, ?array $data = null, array $header = [], int $code = 0) 
    {
        return (new class {
            use ResponseJsonTrait;
            
            public function json($message = null, $data = null, $header = [], $code = 0)
            {
                return $this->success($message, $data, $header, $code);
            }
        })->json($message, $data, $header, $code);
    }
}

if (! function_exists('Larke\\Admin\\error')) {
    /**
     * 返回失败JSON
     *
     * @param string $message 信息
     * @param int    $code    状态码
     * @param array  $data    数据
     * @param array  $header  响应头
     * @return mixed
     *
     * @create 2020-10-19
     * @author deatil
     */
    function error(?string $message = null, int $code = 1, array $data = [], array $header = []) 
    {
        return (new class {
            use ResponseJsonTrait;
            
            public function json($message = null, $code = 1, $data = [], $header = [])
            {
                return $this->error($message, $code, $data, $header);
            }
        })->json($message, $code, $data, $header);
    }
}

if (! function_exists('Larke\\Admin\\route')) {
    /**
     * 后台路由
     *
     * @param string $route     路由
     * @param mixed  $params    请求参数
     * @param bool   $absolute
     * @return mixed
     *
     * @create 2021-2-27
     * @author deatil
     */
    function route(string $route, mixed $params = [], bool $absolute = true) 
    {
        return route(RouteService::formatRouteSlug($route), $params, $absolute);
    }
}

if (! function_exists('Larke\\Admin\\route_name')) {
    /**
     * 获取后台路由别名
     *
     * @param string $route 路由名称
     * @return mixed
     *
     * @create 2021-2-27
     * @author deatil
     */
    function route_name(string $route)
    {
        return RouteService::formatRouteSlug($route);
    }
}

if (! function_exists('Larke\\Admin\\can')) {
    /**
     * 权限判断
     *
     * @param string $slug   路由名称
     * @param string $method 请求方式，大写字母
     * @return mixed
     *
     * @create 2021-5-6
     * @author deatil
     */
    function can(string $slug, string $method = 'GET')
    {
        return app('larke-admin.auth-admin')->hasAccess($slug, $method);
    }
}

if (! function_exists('Larke\\Admin\\authenticate_excepts')) {
    /**
     * 登陆过滤
     *
     * @param array $excepts 权限列表
     * @return mixed
     *
     * @create 2021-3-3
     * @author deatil
     */
    function authenticate_excepts(array $excepts)
    {
        return Extension::authenticateExcepts($excepts);
    }
}

if (! function_exists('Larke\\Admin\\permission_excepts')) {
    /**
     * 权限过滤
     *
     * @param array $excepts 权限列表
     * @return mixed
     *
     * @create 2021-3-3
     * @author deatil
     */
    function permission_excepts(array $excepts)
    {
        return Extension::permissionExcepts($excepts);
    }
}

if (! function_exists('Larke\\Admin\\check_permission')) {
    /**
     * 权限检测
     *
     * @param string $slug   路由name
     * @param string $method 请求方式
     * @return mixed
     *
     * @create 2021-3-22
     * @author deatil
     */
    function check_permission(string $slug, string $method = 'GET')
    {
        return AuthAdmin::hasAccess($slug, $method);
    }
}

if (! function_exists('Larke\\Admin\\config')) {
    /**
     * 配置信息
     *
     * @param string $name    配置关键字
     * @param mixed  $default 默认值
     * @return mixed
     *
     * @create 2020-12-17
     * @author deatil
     */
    function config(string $name, mixed $default = null) 
    {
        $settings =  ConfigModel::getSettings();
        return Arr::get($settings, $name, $default);
    }
}

if (! function_exists('Larke\\Admin\\attachment_url')) {
    /**
     * 附件链接
     *
     * @param string $id      序列号
     * @param mixed  $default 默认
     * @return mixed
     *
     * @create 2020-12-17
     * @author deatil
     */
    function attachment_url(string $id, mixed $default = null) 
    {
        return AttachmentModel::path($id, $default);
    }
}

if (! function_exists('Larke\\Admin\\extension_config')) {
    /**
     * 扩展配置信息
     *
     * @param string $name    扩展包名
     * @param string $key     配置关键字
     * @param mixed  $default 默认值
     * @return mixed
     *
     * @create 2021-3-24
     * @author deatil
     */
    function extension_config(string $name, string $key = null, mixed $default = null) 
    {
        $extensions = ExtensionModel::getExtensions();
        
        $data = Arr::get($extensions, $name, []);
        $config = Arr::get($data, 'config_datas', []);
        
        if (empty($key)) {
            return $config;
        }
        
        return Arr::get($config, $key, $default);
    }
}

if (! function_exists('Larke\\Admin\\add_action')) {
    /**
     * 注册操作
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @param bool   $sort     排序
     * @return $this
     */
    function add_action(string $event, $listener, int $sort = 1): void
    {
        Events::getAction()->listen($event, $listener, $sort);
    }
}

if (! function_exists('Larke\\Admin\\do_action')) {
    /**
     * 触发操作
     * 
     * @param string|object $event 事件名称
     * @param mixed         $var   更多参数
     * @return void
     */
    function do_action($event, ...$var): void
    {
        Events::getAction()->trigger($event, ...$var);
    }
}

if (! function_exists('Larke\\Admin\\remove_action')) {
    /**
     * 移除操作
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @return $this
     */
    function remove_action(string $event, $listener, int $sort = 1): bool
    {
        return Events::getAction()->removeListener($event, $listener, $sort);
    }
}

if (! function_exists('Larke\\Admin\\has_action')) {
    /**
     * 是否有操作
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @return $this
     */
    function has_action(string $event, $listener): bool
    {
        return Events::getAction()->hasListener($event, $listener);
    }
}

if (! function_exists('Larke\\Admin\\add_filter')) {
    /**
     * 注册过滤器
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @param bool   $sort     排序
     * @return $this
     */
    function add_filter(string $event, $listener, int $sort = 1): void
    {
        Events::getFilter()->listen($event, $listener, $sort);
    }
}

if (! function_exists('Larke\\Admin\\apply_filters')) {
    /**
     * 触发过滤器
     * 
     * @param string|object $event  事件名称
     * @param mixed         $params 传入参数
     * @param mixed         $var    更多参数
     * @return mixed
     */
    function apply_filters($event, $params = null, ...$var)
    {
        return Events::getFilter()->trigger($event, $params, ...$var);
    }
}

if (! function_exists('Larke\\Admin\\remove_filter')) {
    /**
     * 移除过滤器
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @return $this
     */
    function remove_filter(string $event, $listener, int $sort = 1): bool
    {
        return Events::getFilter()->removeListener($event, $listener, $sort);
    }
}

if (! function_exists('Larke\\Admin\\has_filter')) {
    /**
     * 是否有过滤器
     * 
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作
     * @return $this
     */
    function has_filter(string $event, $listener): bool
    {
        return Events::getFilter()->hasListener($event, $listener);
    }
}
