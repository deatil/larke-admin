<?php

use Illuminate\Support\Arr;

use Larke\Admin\Model\Config as ConfigModel;
use Larke\Admin\Model\Attachment as AttachmentModel;
use Larke\Admin\Model\Extension as ExtensionModel;
use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;
use Larke\Admin\Service\Route as RouteService;
use Larke\Admin\Facade\Extension;

if (! function_exists('larke_success')) {
    /**
     * 返回成功JSON
     *
     * @create 2020-10-19
     * @author deatil
     */
    function larke_success($message = null, $data = null, $header = [], $code = 0) {
        return (new class {
            use ResponseJsonTrait;
            
            public function json($message = null, $data = null, $header = [], $code = 0)
            {
                return $this->success($message, $data, $header, $code);
            }
        })->json($message, $data, $code, $header);
    }
}

if (! function_exists('larke_error')) {
    /**
     * 返回失败JSON
     *
     * @create 2020-10-19
     * @author deatil
     */
    function larke_error($message = null, $code = 1, $data = [], $header = []) {
        return (new class {
            use ResponseJsonTrait;
            
            public function json($message = null, $code = 1, $data = [], $header = [])
            {
                return $this->error($message, $code, $data, $header);
            }
        })->json($message, $code, $data, $header);
    }
}

if (! function_exists('larke_extension_config')) {
    /**
     * 扩展配置信息
     *
     * @create 2020-12-15
     * @author deatil
     */
    function larke_extension_config($name, $key = null, $default = null) {
        $data = ExtensionModel::where('name', '=', $name)
            ->first()
            ->config_datas;
            
        if (! empty($key)) {
            return Arr::get($data, $key, $default);
        }
        
        return $data;
    }
}

if (! function_exists('larke_config')) {
    /**
     * 配置信息
     *
     * @create 2020-12-17
     * @author deatil
     */
    function larke_config($name, $default = null) {
        $settings =  ConfigModel::getSettings();
        return Arr::get($settings, $name, $default);
    }
}

if (! function_exists('larke_attachment_url')) {
    /**
     * 附件链接
     *
     * @create 2020-12-17
     * @author deatil
     */
    function larke_attachment_url($id, $default = null) {
        return AttachmentModel::path($id, $default);
    }
}

if (! function_exists('larke_admin_route')) {
    /**
     * 后台路由
     *
     * @create 2021-2-27
     * @author deatil
     */
    function larke_admin_route(?string $route, array $params = [], $absolute = true) {
        return route(RouteService::formatRouteSlug($route), $params, $absolute);
    }
}

if (! function_exists('larke_admin_route_name')) {
    /**
     * 获取后台路由别名
     *
     * @create 2021-2-27
     * @author deatil
     */
    function larke_admin_route_name(?string $route)
    {
        return RouteService::formatRouteSlug($route);
    }
}

if (! function_exists('larke_admin_authenticate_excepts')) {
    /**
     * 添加权限过滤
     *
     * @create 2021-3-3
     * @author deatil
     */
    function larke_admin_authenticate_excepts(array $excepts)
    {
        return Extension::authenticateExcepts($excepts);
    }
}

if (! function_exists('larke_admin_permission_excepts')) {
    /**
     * 添加权限过滤
     *
     * @create 2021-3-3
     * @author deatil
     */
    function larke_admin_permission_excepts(array $excepts)
    {
        return Extension::permissionExcepts($excepts);
    }
}
