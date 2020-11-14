<?php

namespace Larke\Admin\Controller;

use Carbon\Carbon;

use Composer\Semver\Semver;
use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use Larke\Admin\Facade\Extension as AdminExtension;
use Larke\Admin\Model\Extension as ExtensionModel;
use Larke\Admin\Service\Upload as UploadService;
use Larke\Admin\Service\PclZip as PclZipService;

/**
 * 扩展
 *
 * @title 扩展
 * @desc 系统扩展管理
 * @order 105
 * @auth true
 *
 * @create 2020-10-30
 * @author deatil
 */
class Extension extends Base
{
    /**
     * 首页
     *
     * @title 扩展列表
     * @desc 系统扩展列表管理
     * @order 101
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $start = $request->get('start', 0);
        $limit = $request->get('limit', 10);
        
        $order = $request->get('order', 'desc');
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        
        $searchword = $request->get('searchword', '');
        $orWheres = [];
        if (! empty($searchword)) {
            $orWheres = [
                ['name', 'like', '%'.$searchword.'%'],
                ['title', 'like', '%'.$searchword.'%'],
                ['author', 'like', '%'.$searchword.'%'],
            ];
        }

        $wheres = [];
        
        $startTime = $request->get('start_time');
        if (! empty($startTime)) {
            $wheres[] = ['installtime', '>=', Carbon::parse($startTime)->timestamp];
        }
        
        $endTime = $request->get('end_time');
        if (! empty($endTime)) {
            $wheres[] = ['installtime', '<=', Carbon::parse($endTime)->timestamp];
        }
        
        $total = ExtensionModel::count(); 
        $list = ExtensionModel::offset($start)
            ->limit($limit)
            ->orWheres($orWheres)
            ->wheres($wheres)
            ->orderBy('installtime', $order)
            ->orderBy('upgradetime', $order)
            ->get()
            ->toArray(); 
        
        return $this->successJson(__('获取成功'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list' => $list,
        ]);
    }
    
    /**
     * 本地全部扩展
     *
     * @title 本地扩展
     * @desc 本地全部扩展
     * @order 102
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function local(Request $request)
    {
        $extensions = AdminExtension::loadExtension()->getExtensions();
        
        $installExtensions = ExtensionModel::getExtensions();
        $extensions = collect($extensions)->map(function($data, $key) use($installExtensions) {
            if (isset($installExtensions[$data['name']])) {
                $data['install'] = $installInfo = $installExtensions[$data['name']];
                
                $infoVersion = Arr::get($data, 'version', 0);
                $installVersion = Arr::get($installInfo, 'version', 0);
                if (Comparator::greaterThan($infoVersion, $installVersion)) {
                    $data['upgrade'] = 1;
                } else {
                    $data['upgrade'] = 0;
                }
            } else {
                $data['install'] = [];
                $data['upgrade'] = 0;
            }
            
            return $data;
        });
        
        return $this->successJson(__('获取成功'), [
            'list' => $extensions,
        ]);
    }
    
    /**
     * install
     *
     * @param  Request  $request
     * @return Response
     */
    public function install(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->errorJson(__('扩展名称不能为空'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (!empty($installInfo)) {
            return $this->errorJson(__('扩展已经安装'));
        }
        
        AdminExtension::loadExtension();
        
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            return $this->errorJson(__('扩展信息不存在'));
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            return $this->errorJson(__('扩展信息不正确'));
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            return $this->errorJson(__('扩展版本信息不正确'));
        }
        
        $adminVersion = config('larke.admin.version');
        $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        if (!$versionCheck) {
            return $this->errorJson(__('扩展适配系统版本错误，当前系统版本：:version', [
                'version' => $adminVersion,
            ]));
        }
        
        $requireExtensions = ExtensionModel::checkRequireExtension($info['require_extension']);
        if (!empty($requireExtensions)) {
            $match = collect($requireExtensions)->contains(function ($data) {
                return ($data['match'] == 0);
            });
            if ($match) {
                return $this->successJson(__('扩展依赖出现错误'), [
                    'require_extensions' => $requireExtensions
                ]);
            }
        }
        
        $extension = ExtensionModel::create([
            'name' => Arr::get($info, 'name'),
            'title' => Arr::get($info, 'title'),
            'introduce' => Arr::get($info, 'introduce'),
            'author' => Arr::get($info, 'author'), 
            'authorsite' => Arr::get($info, 'authorsite'),
            'authoremail' => Arr::get($info, 'authoremail'),
            'version' => Arr::get($info, 'version'),
            'adaptation' => Arr::get($info, 'adaptation'),
            'require_extension' => json_encode(Arr::get($info, 'require_extension', [])),
            'config' => json_encode(Arr::get($info, 'config', [])),
            'class_name' => Arr::get($info, 'class_name'),
            'listorder' => 100,
            'status' => 1,
        ]);
        if ($extension === false) {
            return $this->errorJson(__('安装扩展失败'));
        }
        
        AdminExtension::getNewClassMethod($extension->class_name, 'install');
        
        return $this->successJson(__('安装扩展成功'), [
            'name' => $extension->name
        ]);
    }
    
    /**
     * uninstall
     *
     * @param  Request  $request
     * @return Response
     */
    public function uninstall(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->errorJson(__('扩展名称不能为空'));
        }
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('扩展还没有安装'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->errorJson(__('扩展删除失败'));
        }
        
        AdminExtension::getNewClassMethod($info->class_name, 'uninstall');
        
        return $this->successJson(__('扩展删除成功'));
    }
    
    /**
     * Upgrade
     *
     * @param  Request  $request
     * @return Response
     */
    public function upgrade(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->errorJson(__('扩展名称不能为空'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->errorJson(__('扩展还没有安装'));
        }
        
        AdminExtension::loadExtension();
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            return $this->errorJson(__('扩展信息不存在'));
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            return $this->errorJson(__('扩展信息不正确'));
        }
        
        $adminVersion = config('larke.admin.version');
        $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        if (!$versionCheck) {
            return $this->errorJson(__('扩展适配系统版本错误，当前系统版本：:version', [
                'version' => $adminVersion,
            ]));
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            return $this->errorJson(__('扩展版本信息不正确'));
        }
        
        $infoVersion = Arr::get($info, 'version', 0);
        $installVersion = Arr::get($installInfo, 'version', 0);
        if (!Comparator::greaterThan($infoVersion, $installVersion)) {
            return $this->errorJson(__('扩展不需要更新'));
        }
        
        $requireExtensions = ExtensionModel::checkRequireExtension($info['require_extension']);
        if (!empty($requireExtensions)) {
            $match = collect($requireExtensions)->contains(function ($data) {
                return ($data['match'] == 0);
            });
            if ($match) {
                return $this->successJson(__('扩展依赖出现错误'), [
                    'require_extensions' => $requireExtensions
                ]);
            }
        }
        
        $updateInfo = $installInfo->update([
            'name' => Arr::get($info, 'name'),
            'title' => Arr::get($info, 'title'),
            'introduce' => Arr::get($info, 'introduce'),
            'author' => Arr::get($info, 'author'), 
            'authorsite' => Arr::get($info, 'authorsite'),
            'authoremail' => Arr::get($info, 'authoremail'),
            'version' => Arr::get($info, 'version'),
            'adaptation' => Arr::get($info, 'adaptation'),
            'require_extension' => json_encode(Arr::get($info, 'require_extension', [])),
            'config' => json_encode(Arr::get($info, 'config', [])),
            'class_name' => Arr::get($info, 'class_name'),
            'upgradetime' => time(),
            'listorder' => 100,
            'status' => 1,
        ]);
        if ($updateInfo === false) {
            return $this->errorJson(__('更新扩展失败'));
        }
        
        AdminExtension::getNewClassMethod(Arr::get($info, 'class_name'), 'upgrade');
        
        return $this->successJson(__('更新扩展成功'));
    }
    
    /**
     * 排序
     *
     * @param  Request  $request
     * @return Response
     */
    public function listorder(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->errorJson(__('扩展名称不能为空'));
        }
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('扩展还没有安装'));
        }
        
        $listorder = $request->get('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->errorJson(__('更新扩展排序失败'));
        }
        
        return $this->successJson(__('更新扩展排序成功'));
    }
    
    /**
     * 启用
     *
     * @param  Request  $request
     * @return Response
     */
    public function enable(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->errorJson(__('扩展名称不能为空'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->errorJson(__('扩展还没有安装'));
        }
        
        if ($installInfo['status'] == 1) {
            return $this->errorJson(__('扩展已启用中'));
        }
        
        $status = $installInfo->enable();
        if ($status === false) {
            return $this->errorJson(__('启用扩展失败'));
        }
        
        AdminExtension::getNewClassMethod($installInfo->class_name, 'enable');
        
        return $this->successJson(__('启用扩展成功'));
    }
    
    /**
     * 禁用
     *
     * @param  Request  $request
     * @return Response
     */
    public function disable(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->errorJson(__('扩展名称不能为空'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->errorJson(__('扩展还没有安装'));
        }
        
        if ($installInfo['status'] == 0) {
            return $this->errorJson(__('扩展已禁用中'));
        }
        
        $status = $installInfo->disable();
        if ($status === false) {
            return $this->errorJson(__('禁用扩展失败'));
        }
        
        AdminExtension::getNewClassMethod($installInfo->class_name, 'disable');
        
        return $this->successJson(__('禁用扩展成功'));
    }
    
    /**
     * 配置
     *
     * @param  Request  $request
     * @return Response
     */
    public function config(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->errorJson(__('扩展名称不能为空'));
        }
        
        $config = $request->get('config');
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('扩展还没有安装'));
        }
        
        if (empty(json_decode($config))) {
            return $this->errorJson(__('扩展配置需要为json'));
        }
        
        $status = $info->update([
            'config_data' => $config,
        ]);
        if ($status === false) {
            return $this->errorJson(__('更新扩展配置失败'));
        }
        
        return $this->successJson(__('更新扩展配置成功'));
    }
    
    /**
     * 上传
     *
     * @param  Request  $request
     * @return Response
     */
    public function upload(Request $request)
    {
        $requestFile = $request->file('file');
        if (empty($requestFile)) {
            return $this->errorJson(__('上传文件不能为空'));
        }
        
        // 扩展名
        $extension = $requestFile->extension();
        if ($extension != 'zip') {
            return $this->errorJson(__('上传的文件格式有误！'));
        }
        
        // 原始名称
        $name = $requestFile->getClientOriginalName();
        $extensionPathinfo = pathinfo($name);
        $extensionName = $extensionPathinfo['filename'];
        
        $extensionPath = AdminExtension::getExtensionDirectory($extensionName);
        
        // 检查插件目录是否存在
        if (file_exists($extensionPath)) {
            return $this->errorJson(__('扩展已经存在'));
        }
        
        // 解压文件
        $filename = $requestFile->getPathname();
        $zip = new PclZipService($filename);
        $status = $zip->extract(PCLZIP_OPT_PATH, $extensionPath);
        if (!$status) {
            return $this->errorJson(__('扩展解压失败'));
        }
        
        return $this->successJson(__('扩展上传成功！'));
    }
    
}
