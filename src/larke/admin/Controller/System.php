<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

/**
 * 系统
 *
 * @title 系统
 * @desc 系统管理
 * @order 103
 * @auth true
 *
 * @create 2020-10-25
 * @author deatil
 */
class System extends Base
{
    /**
     * 系统详情
     *
     * @param  Request  $request
     * @return Response
     */
    public function info(Request $request)
    {
        $info = [
            'admin' => config('larke.admin'),
            'sys' => $this->getSysInfo(),
        ];
        return $this->successJson(__('获取成功'), $info);
    }
    
    /**
     * 清除缓存
     *
     * @param  Request  $request
     * @return Response
     */
    public function clearCache(Request $request)
    {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        
        return $this->successJson(__('清除缓存成功'));
    }
    
    /**
     * 设置缓存
     *
     * @param  Request  $request
     * @return Response
     */
    public function cache(Request $request)
    {
        Artisan::call('route:cache');
        Artisan::call('config:cache');
        
        return $this->successJson(__('路由及配置信息缓存成功'));
    }

    /**
     * phpinfo信息
     */
    protected function getSysInfo()
    {
        $sysInfo['domain'] = $_SERVER['HTTP_HOST']; //域名
        $sysInfo['ip'] = GetHostByName($_SERVER['SERVER_NAME']); //服务器IP
        $sysInfo['os'] = PHP_OS; //操作系统
        $sysInfo['php_uname'] = php_uname();
        $sysInfo['php_version'] = phpversion(); //php版本
        $sysInfo['web_server'] = $_SERVER['SERVER_SOFTWARE']; //服务器环境
        $sysInfo['web_directory'] = $_SERVER["DOCUMENT_ROOT"]; //网站目录
        $mysqlinfo = Db::select("SELECT VERSION() as version");
        $sysInfo['mysql_version'] = $mysqlinfo[0]->version;
        $sysInfo['file_upload'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknown'; //文件上传限制
        //$sysInfo['memory_limit'] = ini_get('memory_limit'); //最大占用内存
        //$sysInfo['set_time_limit'] = function_exists("set_time_limit") ? true : false; //最大执行时间
        $sysInfo['zlib'] = function_exists('gzclose') ? 'YES' : 'NO'; //Zlib支持
        //$sysInfo['safe_mode'] = (boolean) ini_get('safe_mode') ? 'YES' : 'NO'; //安全模式
        $sysInfo['timezone'] = function_exists("date_default_timezone_get") ? date_default_timezone_get() : "no_timezone";
        $sysInfo['curl'] = function_exists('curl_init') ? 'YES' : 'NO'; //Curl支持
        //$sysInfo['max_ex_time'] = @ini_get("max_execution_time") . 's';
        //$sysInfo['remaining_space'] = round((disk_free_space(".") / (1024 * 1024)), 2) . 'M'; //剩余空间
        $sysInfo['request_ip'] = request()->ip(); //用户IP地址
        $sysInfo['now_time'] = gmdate("Y-m-d H:i:s", time() + 8 * 3600); //北京时间
        $sysInfo['time'] = date("Y-m-d H:i:s"); //服务器时间
        if (function_exists("gd_info")) {
            //GD库版本
            $gd = gd_info();
            $sysInfo['gdinfo'] = $gd['GD Version'];
        } else {
            $sysInfo['gdinfo'] = __("未知");
        }
        return $sysInfo;
    }
    
}
