<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Event\SystemInfo as SystemInfoEvent;
use Larke\Admin\Event\SystemClearCache as SystemClearCacheEvent;
use Larke\Admin\Event\SystemCache as SystemCacheEvent;

/**
 * 系统
 *
 * @title 系统
 * @desc 系统管理
 * @order 200
 * @auth true
 * @slug {prefixAs}system
 *
 * @create 2020-10-25
 * @author deatil
 */
class System extends Base
{
    /**
     * 系统详情
     *
     * @title 系统详情
     * @desc 系统详情管理
     * @order 201
     * @auth true
     *
     * @return Response
     */
    public function info()
    {
        $info = [
            'admin' => config('larkeadmin.admin'),
            'system' => $this->getSysInfo(),
        ];
        
        $eventInfo = event(new SystemInfoEvent($info));
        if (!empty($eventInfo) && is_array($eventInfo)) {
            $info = array_merge($info, $eventInfo);
        }
        
        return $this->success(__('获取成功'), $info);
    }
    
    /**
     * 清除缓存
     *
     * @title 清除缓存
     * @desc 清除缓存管理
     * @order 202
     * @auth true
     *
     * @return Response
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        
        event(new SystemClearCacheEvent());
        
        return $this->success(__('清除缓存成功'));
    }
    
    /**
     * 设置缓存
     *
     * @title 设置缓存
     * @desc 设置缓存管理
     * @order 203
     * @auth true
     *
     * @return Response
     */
    public function cache()
    {
        Artisan::call('route:cache');
        Artisan::call('config:cache');
        
        event(new SystemCacheEvent());
        
        return $this->success(__('路由及配置信息缓存成功'));
    }
    
    /**
     * 语言包
     *
     * @title 语言包
     * @desc 语言包管理
     * @order 204
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function lang(Request $request)
    {
        $group = $request->input('group');
        
        $validator = Validator::make([
            'group' => $group,
        ], [
            'group' => 'required|alpha_num',
        ], [
            'group.required' => __('请选择要查询的语言分组'),
            'group.alpha_num' => __('语言分组名称格式错误'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $translator = app('translator');
        
        $locale = $request->input('locale');
        if (!empty($locale)) {
            $validator = Validator::make([
                'locale' => $locale,
            ], [
                'locale' => 'required|alpha_dash',
            ], [
                'locale.required' => __('请选择要查询的语言'),
                'locale.alpha_dash' => __('语言名称格式错误'),
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors()->first());
            }
        } else {
            $locale = $translator->getLocale();
        }
        
        // for json
        $langs = $translator->getLoader()->load($locale, $group, '*');
        if (empty($langs)) {
            // for file
            $langs = $translator->getLoader()->load($locale, $group);
        }
        
        return $this->success(__('获取成功'), [
            'list' => $langs,
        ]);
    }
    
    /**
     * 设置默认语言
     *
     * @title 语言设置
     * @desc 设置系统默认语言
     * @order 205
     * @auth true
     *
     * @return Response
     */
    public function setLang(string $locale)
    {
        $validator = Validator::make([
            'locale' => $locale,
        ], [
            'locale' => 'required|alpha_dash',
        ], [
            'locale.required' => __('设置的语言不能为空'),
            'locale.alpha_dash' => __('设置的语言格式错误'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        // 设置语言缓存
        Cache::put('locale-language', $locale);
        
        return $this->success(__('设置默认语言成功'));
    }

    /**
     * phpinfo信息
     */
    protected function getSysInfo()
    {
        $sysInfo['domain'] = $_SERVER['HTTP_HOST']; // 域名
        $sysInfo['ip'] = GetHostByName($_SERVER['SERVER_NAME']); // 服务器IP
        $sysInfo['os'] = PHP_OS; // 操作系统
        $sysInfo['php_uname'] = php_uname();
        $sysInfo['php_version'] = phpversion(); // php版本
        $sysInfo['web_server'] = $_SERVER['SERVER_SOFTWARE']; // 服务器环境
        $sysInfo['web_directory'] = $_SERVER["DOCUMENT_ROOT"]; // 网站目录
        $mysqlinfo = Db::select("SELECT VERSION() as version");
        $sysInfo['mysql_version'] = $mysqlinfo[0]->version;
        $sysInfo['file_upload'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknown'; // 文件上传限制
        //$sysInfo['memory_limit'] = ini_get('memory_limit'); // 最大占用内存
        //$sysInfo['set_time_limit'] = function_exists("set_time_limit") ? true : false; // 最大执行时间
        $sysInfo['zlib'] = function_exists('gzclose') ? 'YES' : 'NO'; // Zlib支持
        //$sysInfo['safe_mode'] = (boolean) ini_get('safe_mode') ? 'YES' : 'NO'; // 安全模式
        $sysInfo['timezone'] = function_exists("date_default_timezone_get") ? date_default_timezone_get() : "no_timezone";
        $sysInfo['curl'] = function_exists('curl_init') ? 'YES' : 'NO'; // Curl支持
        //$sysInfo['max_ex_time'] = @ini_get("max_execution_time") . 's';
        //$sysInfo['remaining_space'] = round((disk_free_space(".") / (1024 * 1024)), 2) . 'M'; // 剩余空间
        $sysInfo['request_ip'] = request()->ip(); // 用户IP地址
        $sysInfo['now_time'] = gmdate("Y-m-d H:i:s", time() + 8 * 3600); //北京时间
        $sysInfo['time'] = date("Y-m-d H:i:s"); // 服务器时间
        if (function_exists("gd_info")) {
            // GD库版本
            $gd = gd_info();
            $sysInfo['gdinfo'] = $gd['GD Version'];
        } else {
            $sysInfo['gdinfo'] = __("未知");
        }
        
        $sysInfo['laravel'] = app()->version(); // laravel版本
        
        return $sysInfo;
    }
    
}
