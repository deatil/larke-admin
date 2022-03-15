<?php

declare (strict_types = 1);

namespace Larke\Admin;

use Illuminate\Support\Arr;

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
     * @param array $data 数据
     * @param array $header 响应头
     * @param int $code 状态码
     * @return mix
     *
     * @create 2020-10-19
     * @author deatil
     */
    function success($message = null, $data = null, $header = [], $code = 0) {
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
     * @param int $code 状态码
     * @param array $data 数据
     * @param array $header 响应头
     * @return mix
     *
     * @create 2020-10-19
     * @author deatil
     */
    function error($message = null, $code = 1, $data = [], $header = []) {
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
     * @param string $route 路由
     * @param string $params 请求参数
     * @return mix
     *
     * @create 2021-2-27
     * @author deatil
     */
    function route(?string $route, array $params = [], $absolute = true) {
        return route(RouteService::formatRouteSlug($route), $params, $absolute);
    }
}

if (! function_exists('Larke\\Admin\\route_name')) {
    /**
     * 获取后台路由别名
     *
     * @param string $route 路由名称
     * @return mix
     *
     * @create 2021-2-27
     * @author deatil
     */
    function route_name(?string $route)
    {
        return RouteService::formatRouteSlug($route);
    }
}

if (! function_exists('Larke\\Admin\\can')) {
    /**
     * 权限判断
     *
     * @param string $slug 路由名称
     * @param string $method 请求方式，大写字母
     * @return mix
     *
     * @create 2021-5-6
     * @author deatil
     */
    function can($slug, $method = 'GET')
    {
        return app('larke-admin.auth-admin')->hasAccess($slug, $method);
    }
}

if (! function_exists('Larke\\Admin\\authenticate_excepts')) {
    /**
     * 登陆过滤
     *
     * @param array $excepts 权限列表
     * @return mix
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
     * @return mix
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
     * @param string $slug 路由name
     * @param string $method 请求方式
     * @return mix
     *
     * @create 2021-3-22
     * @author deatil
     */
    function check_permission($slug, $method = 'GET')
    {
        return AuthAdmin::hasAccess($slug, $method);
    }
}

if (! function_exists('Larke\\Admin\\config')) {
    /**
     * 配置信息
     *
     * @param string $name 配置关键字
     * @param string $default 默认值
     * @return mix
     *
     * @create 2020-12-17
     * @author deatil
     */
    function config($name, $default = null) {
        $settings =  ConfigModel::getSettings();
        return Arr::get($settings, $name, $default);
    }
}

if (! function_exists('Larke\\Admin\\attachment_url')) {
    /**
     * 附件链接
     *
     * @param string $id 序列号
     * @param string $default 默认
     * @return mix
     *
     * @create 2020-12-17
     * @author deatil
     */
    function attachment_url($id, $default = null) {
        return AttachmentModel::path($id, $default);
    }
}

if (! function_exists('Larke\\Admin\\extension_config')) {
    /**
     * 扩展配置信息
     *
     * @param string $name 扩展包名
     * @param string $key 配置关键字
     * @param string $default 默认值
     * @return mix
     *
     * @create 2021-3-24
     * @author deatil
     */
    function extension_config($name, $key = null, $default = null) {
        $extensions = ExtensionModel::getExtensions();
        
        $data = Arr::get($extensions, $name, []);
        $config = Arr::get($data, 'config_datas', []);
        
        if (empty($key)) {
            return $config;
        }
        
        return Arr::get($config, $key, $default);
    }
}
