<?php

namespace Larke\Admin\Controller;

use Composer\Semver\Semver;
use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use Larke\Admin\Event;
use Larke\Admin\Support\PclZip;
use Larke\Admin\Facade\Extension as AdminExtension;
use Larke\Admin\Model\Extension as ExtensionModel;

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
     * 扩展列表
     *
     * @title 扩展列表
     * @desc 系统扩展列表管理
     * @order 1051
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $start = (int) $request->get('start', 0);
        $limit = (int) $request->get('limit', 10);
        
        $order = $this->formatOrderBy($request->get('order', 'ASC'));
        
        $searchword = $request->get('searchword', '');
        $orWheres = [];
        if (! empty($searchword)) {
            $orWheres = [
                ['name', 'like', '%'.$searchword.'%'],
                ['title', 'like', '%'.$searchword.'%'],
            ];
        }

        $wheres = [];
        
        $startTime = $this->formatDate($request->get('start_time'));
        if ($startTime !== false) {
            $wheres[] = ['create_time', '>=', $startTime];
        }
        
        $endTime = $this->formatDate($request->get('end_time'));
        if ($endTime !== false) {
            $wheres[] = ['create_time', '<=', $endTime];
        }
        
        $status = $this->switchStatus($request->get('status'));
        if ($status !== false) {
            $wheres[] = ['status', $status];
        }
        
        $query = ExtensionModel::orWheres($orWheres)
            ->wheres($wheres);
        
        $total = $query->count(); 
        $list = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy('installtime', $order)
            ->orderBy('upgradetime', $order)
            ->get()
            ->toArray(); 
        
        return $this->success(__('获取成功'), [
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
     * @order 1052
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
        
        return $this->success(__('获取成功'), [
            'list' => $extensions,
        ]);
    }
    
    /**
     * 安装
     *
     * @title 扩展安装
     * @desc 系统扩展安装
     * @order 1053
     * @auth true
     *
     * @param string $name
     * @return Response
     */
    public function install(string $name)
    {
        if (empty($name)) {
            return $this->error(__('扩展名称不能为空'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (!empty($installInfo)) {
            return $this->error(__('扩展已经安装'));
        }
        
        AdminExtension::loadExtension();
        
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            return $this->error(__('扩展信息不存在'));
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            return $this->error(__('扩展信息不正确'));
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            return $this->error(__('扩展版本信息不正确'));
        }
        
        $adminVersion = config('larkeadmin.admin.version');
        $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        if (!$versionCheck) {
            return $this->error(__('扩展适配系统版本错误，当前系统版本：:version', [
                'version' => $adminVersion,
            ]));
        }
        
        $requireExtensions = ExtensionModel::checkRequireExtension($info['require_extension']);
        if (!empty($requireExtensions)) {
            $match = collect($requireExtensions)->contains(function ($data) {
                return ($data['match'] == 0);
            });
            if ($match) {
                return $this->success(__('扩展依赖出现错误'), [
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
            return $this->error(__('安装扩展失败'));
        }
        
        AdminExtension::getNewClassMethod($extension->class_name, 'install');
        
        return $this->success(__('安装扩展成功'), [
            'name' => $extension->name
        ]);
    }
    
    /**
     * 卸载
     *
     * @title 扩展卸载
     * @desc 系统扩展卸载
     * @order 1054
     * @auth true
     *
     * @param string $name
     * @return Response
     */
    public function uninstall(string $name)
    {
        if (empty($name)) {
            return $this->error(__('扩展名称不能为空'));
        }
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->error(__('扩展还没有安装'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('扩展删除失败'));
        }
        
        AdminExtension::getNewClassMethod($info->class_name, 'uninstall');
        
        return $this->success(__('扩展删除成功'));
    }
    
    /**
     * 更新
     *
     * @title 扩展更新
     * @desc 系统扩展更新
     * @order 1055
     * @auth true
     *
     * @param string $name
     * @return Response
     */
    public function upgrade(string $name)
    {
        if (empty($name)) {
            return $this->error(__('扩展名称不能为空'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->error(__('扩展还没有安装'));
        }
        
        AdminExtension::loadExtension();
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            return $this->error(__('扩展信息不存在'));
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            return $this->error(__('扩展信息不正确'));
        }
        
        $adminVersion = config('larkeadmin.admin.version');
        $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        if (!$versionCheck) {
            return $this->error(__('扩展适配系统版本错误，当前系统版本：:version', [
                'version' => $adminVersion,
            ]));
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            return $this->error(__('扩展版本信息不正确'));
        }
        
        $infoVersion = Arr::get($info, 'version', 0);
        $installVersion = Arr::get($installInfo, 'version', 0);
        if (!Comparator::greaterThan($infoVersion, $installVersion)) {
            return $this->error(__('扩展不需要更新'));
        }
        
        $requireExtensions = ExtensionModel::checkRequireExtension($info['require_extension']);
        if (!empty($requireExtensions)) {
            $match = collect($requireExtensions)->contains(function ($data) {
                return ($data['match'] == 0);
            });
            if ($match) {
                return $this->success(__('扩展依赖出现错误'), [
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
        ]);
        if ($updateInfo === false) {
            return $this->error(__('更新扩展失败'));
        }
        
        AdminExtension::getNewClassMethod(Arr::get($info, 'class_name'), 'upgrade');
        
        return $this->success(__('更新扩展成功'));
    }
    
    /**
     * 排序
     *
     * @title 扩展排序
     * @desc 系统扩展排序
     * @order 1056
     * @auth true
     *
     * @param string $name
     * @param Request $request
     * @return Response
     */
    public function listorder(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->error(__('扩展名称不能为空'));
        }
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->error(__('扩展还没有安装'));
        }
        
        $listorder = $request->get('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->error(__('更新扩展排序失败'));
        }
        
        return $this->success(__('更新扩展排序成功'));
    }
    
    /**
     * 启用
     *
     * @title 扩展启用
     * @desc 系统扩展启用
     * @order 1057
     * @auth true
     *
     * @param string $name
     * @return Response
     */
    public function enable(string $name)
    {
        if (empty($name)) {
            return $this->error(__('扩展名称不能为空'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->error(__('扩展还没有安装'));
        }
        
        if ($installInfo['status'] == 1) {
            return $this->error(__('扩展已启用中'));
        }
        
        $status = $installInfo->enable();
        if ($status === false) {
            return $this->error(__('启用扩展失败'));
        }
        
        AdminExtension::getNewClassMethod($installInfo->class_name, 'enable');
        
        return $this->success(__('启用扩展成功'));
    }
    
    /**
     * 禁用
     *
     * @title 扩展禁用
     * @desc 系统扩展禁用
     * @order 1058
     * @auth true
     *
     * @param string $name
     * @return Response
     */
    public function disable(string $name)
    {
        if (empty($name)) {
            return $this->error(__('扩展名称不能为空'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->error(__('扩展还没有安装'));
        }
        
        if ($installInfo['status'] == 0) {
            return $this->error(__('扩展已禁用中'));
        }
        
        $status = $installInfo->disable();
        if ($status === false) {
            return $this->error(__('禁用扩展失败'));
        }
        
        AdminExtension::getNewClassMethod($installInfo->class_name, 'disable');
        
        return $this->success(__('禁用扩展成功'));
    }
    
    /**
     * 配置
     *
     * @title 扩展配置
     * @desc 系统扩展配置
     * @order 1058
     * @auth true
     *
     * @param string $name
     * @param Request $request
     * @return Response
     */
    public function config(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->error(__('扩展名称不能为空'));
        }
        
        event(new Event\ExtensionConfigBefore($request));
        
        $config = $request->get('config');
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->error(__('扩展还没有安装'));
        }
        
        if (empty(json_decode($config))) {
            return $this->error(__('扩展配置需要为json'));
        }
        
        $status = $info->update([
            'config_data' => $config,
        ]);
        if ($status === false) {
            return $this->error(__('更新扩展配置失败'));
        }
        
        event(new Event\ExtensionConfigAfter($info));
        
        return $this->success(__('更新扩展配置成功'));
    }
    
    /**
     * 上传
     *
     * @title 扩展上传
     * @desc 系统扩展上传
     * @order 1059
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function upload(Request $request)
    {
        $requestFile = $request->file('file');
        if (empty($requestFile)) {
            return $this->error(__('上传文件不能为空'));
        }
        
        // 扩展名
        $extension = $requestFile->extension();
        if ($extension != 'zip') {
            return $this->error(__('上传的文件格式有误！'));
        }
        
        // 原始名称
        $name = $requestFile->getClientOriginalName();
        $extensionPathinfo = pathinfo($name);
        $extensionName = $extensionPathinfo['filename'];
        
        $extensionPath = AdminExtension::getExtensionDirectory($extensionName);
        
        // 检查扩展目录是否存在
        if (file_exists($extensionPath)) {
            return $this->error(__('扩展已经存在'));
        }
        
        // 解压文件
        $filename = $requestFile->getPathname();
        $zip = new PclZip($filename);
        $status = $zip->extract(PCLZIP_OPT_PATH, $extensionPath);
        if (!$status) {
            return $this->error(__('扩展解压失败'));
        }
        
        return $this->success(__('扩展上传成功！'));
    }
    
}
