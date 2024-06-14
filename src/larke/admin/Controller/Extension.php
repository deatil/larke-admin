<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Composer\Semver\Semver;
use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Event;
use Larke\Admin\Support\PclZip;
use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Composer\Repository as ComposerRepository;
use Larke\Admin\Facade\Extension as AdminExtension;
use Larke\Admin\Model\Extension as ExtensionModel;

/**
 * 扩展
 *
 * @create 2020-10-30
 * @author deatil
 */
#[RouteRule(
    title: "扩展", 
    desc:  "系统扩展管理",
    order: 560,
    auth:  true,
    slug:  "{prefix}extension"
)]
class Extension extends Base
{
    /**
     * 扩展列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "扩展列表", 
        desc:  "系统扩展列表管理",
        order: 561,
        auth:  true
    )]
    public function index(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $limit = (int) $request->input('limit', 10);
        
        $order = $this->formatOrderBy($request->input('order', 'create_time__ASC'));
        
        $orWheres = [];

        $searchword = $request->input('searchword', '');
        if (! empty($searchword)) {
            $orWheres = [
                ['name', 'like', '%'.$searchword.'%'],
                ['title', 'like', '%'.$searchword.'%'],
            ];
        }

        $wheres = [];
        
        $startTime = $this->formatDate($request->input('start_time'));
        if ($startTime !== false) {
            $wheres[] = ['create_time', '>=', $startTime];
        }
        
        $endTime = $this->formatDate($request->input('end_time'));
        if ($endTime !== false) {
            $wheres[] = ['create_time', '<=', $endTime];
        }
        
        $status = $this->switchStatus($request->input('status'));
        if ($status !== false) {
            $wheres[] = ['status', $status];
        }
        
        // 查询
        $query = ExtensionModel::wheres($wheres)
            ->orWheres($orWheres);
        
        $total = $query->count(); 
        $list = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy($order[0], $order[1])
            ->get()
            ->toArray(); 
        
        // 添加icon图标
        $list = collect($list)
            ->map(function($data, $key) {
                $icon = '';
                if (class_exists($data['class_name'])) {
                    $newClass = app()->register($data['class_name']);
                    if (isset($newClass->icon)) {
                        $icon = $newClass->icon;
                    }
                }
                
                $data['icon'] = AdminExtension::getIcon($icon);
                
                return $data;
            })
            ->toArray();
        
        return $this->success(__('larke-admin::common.get_success'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list'  => $list,
        ]);
    }
    
    /**
     * 本地全部扩展
     *
     * @return Response
     */
    #[RouteRule(
        title: "本地扩展", 
        desc:  "本地全部扩展",
        order: 562,
        auth:  true
    )]
    public function local()
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
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $extensions,
        ]);
    }
    
    /**
     * 刷新本地扩展
     *
     * @return Response
     */
    #[RouteRule(
        title: "刷新扩展", 
        desc:  "刷新本地扩展",
        order: 563,
        auth:  true
    )]
    public function refreshLocal()
    {
        AdminExtension::refresh();
        
        return $this->success(__('larke-admin::extension.extension_refresh_success'));
    }
    
    /**
     * 本地扩展命令
     *
     * @param string $name
     * @return Response
     */
    #[RouteRule(
        title: "本地扩展命令", 
        desc:  "本地扩展命令，只限用于非composer扩展",
        order: 564,
        auth:  true
    )]
    public function command(string $name)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->error(__('larke-admin::extension.extension_dont_install'));
        }
        
        $require = AdminExtension::composerRequireCommand($name);
        $remove = AdminExtension::composerRemoveCommand($name);
        
        // 判断仓库是否注册本地扩展
        $hasRepository = ComposerRepository::create()
            ->withDirectory(base_path())
            ->has($name);
        
        $command = [
            'require' => $require,
            'remove' => $remove,
            'has_repository' => $hasRepository,
        ];
        
        return $this->success(__('larke-admin::common.get_success'), [
            'command' => $command,
        ]);
    }
    
    /**
     * 安装
     *
     * @param string $name
     * @return Response
     */
    #[RouteRule(
        title: "扩展安装", 
        desc:  "系统扩展安装",
        order: 565,
        auth:  true
    )]
    public function install(string $name)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (! empty($installInfo)) {
            return $this->error(__('larke-admin::extension.extension_installed'));
        }
        
        AdminExtension::loadExtension();
        
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            return $this->error(__('larke-admin::extension.extension_info_empty'));
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            return $this->error(__('larke-admin::extension.extension_info_error'));
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            return $this->error(__('larke-admin::extension.extension_version_error'));
        }
        
        $adminVersion = config('larkeadmin.admin.version');
        
        try {
            $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        } catch(\Exception $e) {
            return $this->error(__('larke-admin::extension.extension_adaptation_error'));
        }
        
        if (! $versionCheck) {
            return $this->error(__('larke-admin::extension.extension_adaptation_info_error', [
                'version' => $adminVersion,
            ]));
        }
        
        $requireExtensions = ExtensionModel::checkRequireExtension($info['require']);
        if (!empty($requireExtensions)) {
            $match = collect($requireExtensions)->contains(function ($data) {
                return ($data['match'] === false);
            });
            if ($match) {
                return $this->error(__('larke-admin::extension.extension_require_error', [
                    'require' => $requireExtensions[0]['name'] . '[' . $requireExtensions[0]['version'] . ']',
                ]), \ResponseCode::EXTENSION_NOT_MATCH);
            }
        }
        
        $extension = ExtensionModel::create([
            'name'        => Arr::get($info, 'name'),
            'title'       => Arr::get($info, 'title'),
            'description' => Arr::get($info, 'description'),
            'keywords'    => json_encode(Arr::get($info, 'keywords', [])), 
            'homepage'    => Arr::get($info, 'homepage'),
            'authors'     => json_encode(Arr::get($info, 'authors', [])),
            'version'     => Arr::get($info, 'version'),
            'adaptation'  => Arr::get($info, 'adaptation'),
            'require'     => json_encode(Arr::get($info, 'require', [])),
            'config'      => json_encode(Arr::get($info, 'config', [])),
            'class_name'  => Arr::get($info, 'class_name'),
        ]);
        if ($extension === false) {
            return $this->error(__('larke-admin::extension.instell_extension_fail'));
        }
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
        
        // 安装事件
        event(new Event\ExtensionInstall($name, $info));
        
        return $this->success(__('larke-admin::extension.instell_extension_success'), [
            'name' => $extension->name
        ]);
    }
    
    /**
     * 卸载
     *
     * @param string $name
     * @return Response
     */
    #[RouteRule(
        title: "扩展卸载", 
        desc:  "系统扩展卸载",
        order: 566,
        auth:  true
    )]
    public function uninstall(string $name)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::extension.extension_dont_install'));
        }
        
        if ($info->status == 1) {
            return $this->error(__('larke-admin::extension.uninstell_extension_need_disable'));
        }

        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('larke-admin::extension.uninstell_extension_fail'));
        }
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
        
        AdminExtension::loadExtension();
        
        // 卸载事件
        event(new Event\ExtensionUninstall($name, $info->toArray()));
        
        return $this->success(__('larke-admin::extension.uninstell_extension_success'));
    }
    
    /**
     * 更新
     *
     * @param string $name
     * @return Response
     */
    #[RouteRule(
        title: "扩展更新", 
        desc:  "系统扩展更新",
        order: 567,
        auth:  true
    )]
    public function upgrade(string $name)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->error(__('larke-admin::extension.extension_dont_install'));
        }
        
        if ($installInfo->status == 1) {
            return $this->error(__('larke-admin::extension.upgrade_extension_need_disable'));
        }

        AdminExtension::loadExtension();
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            return $this->error(__('larke-admin::extension.extension_info_empty'));
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            return $this->error(__('larke-admin::extension.extension_info_error'));
        }
        
        $adminVersion = config('larkeadmin.admin.version');
        
        try {
            $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        } catch(\Exception $e) {
            return $this->error(__('larke-admin::extension.extension_adaptation_error'));
        }
        
        if (! $versionCheck) {
            return $this->error(__('larke-admin::extension.extension_adaptation_info_error', [
                'version' => $adminVersion,
            ]));
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            return $this->error(__('larke-admin::extension.extension_version_info_error'));
        }
        
        $infoVersion = Arr::get($info, 'version', 0);
        $installVersion = Arr::get($installInfo, 'version', 0);
        if (!Comparator::greaterThan($infoVersion, $installVersion)) {
            return $this->error(__('larke-admin::extension.extension_dont_need_upgrade'));
        }
        
        $requireExtensions = ExtensionModel::checkRequireExtension($info['require']);
        if (!empty($requireExtensions)) {
            $match = collect($requireExtensions)->contains(function ($data) {
                return ($data['match'] === false);
            });
            if ($match) {
                return $this->error(__('larke-admin::extension.extension_require_error', [
                    'require' => $requireExtensions[0]['name'] . '[' . $requireExtensions[0]['version'] . ']',
                ]), \ResponseCode::EXTENSION_NOT_MATCH);
            }
        }
        
        $updateInfo = $installInfo->update([
            'name'        => Arr::get($info, 'name'),
            'title'       => Arr::get($info, 'title'),
            'description' => Arr::get($info, 'description'),
            'keywords'    => json_encode(Arr::get($info, 'keywords')), 
            'homepage'    => Arr::get($info, 'homepage'),
            'authors'     => json_encode(Arr::get($info, 'authors', [])),
            'version'     => Arr::get($info, 'version'),
            'adaptation'  => Arr::get($info, 'adaptation'),
            'require'     => json_encode(Arr::get($info, 'require', [])),
            'config'      => json_encode(Arr::get($info, 'config', [])),
            'class_name'  => Arr::get($info, 'class_name'),
            'upgradetime' => time(),
        ]);
        if ($updateInfo === false) {
            return $this->error(__('larke-admin::extension.upgrade_extension_fail'));
        }
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
        
        // 更新事件
        event(new Event\ExtensionUpgrade($name, $installInfo->toArray(), $info));
        
        return $this->success(__('larke-admin::extension.upgrade_extension_success'));
    }
    
    /**
     * 排序
     *
     * @param string $name
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "扩展排序", 
        desc:  "系统扩展排序",
        order: 568,
        auth:  true
    )]
    public function listorder(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::extension.extension_dont_install'));
        }
        
        $listorder = $request->input('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->error(__('larke-admin::extension.upgrade_extension_sort_fail'));
        }
        
        return $this->success(__('larke-admin::extension.upgrade_extension_sort_success'));
    }
    
    /**
     * 启用
     *
     * @param string $name
     * @return Response
     */
    #[RouteRule(
        title: "扩展启用", 
        desc:  "系统扩展启用",
        order: 569,
        auth:  true
    )]
    public function enable(string $name)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->error(__('larke-admin::extension.extension_dont_install'));
        }
        
        if ($installInfo['status'] == 1) {
            return $this->error(__('larke-admin::extension.extension_enabled'));
        }
        
        $status = $installInfo->enable();
        if ($status === false) {
            return $this->error(__('larke-admin::extension.enable_extension_fail'));
        }
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
        
        AdminExtension::loadExtension();
        
        // 启用事件
        event(new Event\ExtensionEnable($name, $installInfo->toArray()));
        
        return $this->success(__('larke-admin::extension.enable_extension_success'));
    }
    
    /**
     * 禁用
     *
     * @param string $name
     * @return Response
     */
    #[RouteRule(
        title: "扩展禁用", 
        desc:  "系统扩展禁用",
        order: 570,
        auth:  true
    )]
    public function disable(string $name)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            return $this->error(__('larke-admin::extension.extension_dont_install'));
        }
        
        if ($installInfo['status'] == 0) {
            return $this->error(__('larke-admin::extension.extension_disabled'));
        }
        
        $status = $installInfo->disable();
        if ($status === false) {
            return $this->error(__('larke-admin::extension.disable_extension_fail'));
        }
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
        
        // 禁用事件
        event(new Event\ExtensionDisable($name, $installInfo->toArray()));
        
        return $this->success(__('larke-admin::extension.disable_extension_success'));
    }
    
    /**
     * 配置
     *
     * @param string $name
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "扩展配置", 
        desc:  "系统扩展配置",
        order: 571,
        auth:  true
    )]
    public function config(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        event(new Event\ExtensionConfigBefore($name, $request));
        
        $config = $request->input('config');
        
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::extension.extension_dont_install'));
        }
        
        if (empty(json_decode($config))) {
            return $this->error(__('larke-admin::extension.extension_config_error'));
        }
        
        $status = $info->update([
            'config_data' => $config,
        ]);
        if ($status === false) {
            return $this->error(__('larke-admin::extension.update_extension_config_fail'));
        }
        
        event(new Event\ExtensionConfigAfter($name, $info));
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
        
        return $this->success(__('larke-admin::extension.update_extension_config_success'));
    }
    
    /**
     * 上传
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "扩展上传", 
        desc:  "扩展压缩包上传",
        order: 572,
        auth:  true
    )]
    public function upload(Request $request)
    {
        $requestFile = $request->file('file');
        if (empty($requestFile)) {
            return $this->error(__('larke-admin::extension.upload_extension_file_dont_empty'));
        }
        
        // 扩展名
        $extension = $requestFile->extension();
        if ($extension != 'zip') {
            return $this->error(__('larke-admin::extension.upload_extension_file_ext_error'));
        }
        
        // 缓存目录
        if (!defined('PCLZIP_TEMPORARY_DIR')) {
            define('PCLZIP_TEMPORARY_DIR', storage_path('tmp'));
        }
        
        // 解析composer.json
        $filename = $requestFile->getPathname();
        $zip = new PclZip($filename);
        
        $list = $zip->listContent();
        if ($list == 0) {
            return $this->error(__('larke-admin::extension.upload_extension_file_error'));
        }
        
        $composer = collect($list)
            ->map(function($item) {
                if (strpos($item['filename'], 'composer.json') !== false) {
                    return $item;
                }
            })
            ->filter(function($data) {
                return !empty($data);
            })
            ->sortBy(function($item) {
                $item['filename'] = str_replace('\\', '/', $item['filename']);
                return count(explode('/', $item['filename']));
            })
            ->values()
            ->toArray();
        
        if (empty($composer)) {
            return $this->error(__('larke-admin::extension.extension_composer_not_exists'));
        }
        
        $data = $zip->extractByIndex($composer[0]['index'], PCLZIP_OPT_EXTRACT_AS_STRING);
        if ($data == 0) {
            return $this->error(__('larke-admin::extension.upload_extension_file_error'));
        }
        
        try {
            $composerInfo = json_decode($data[0]['content'], true);
        } catch(\Exception $e) {
            return $this->error(__('larke-admin::extension.extension_composer_error'));
        }
        
        if (! isset($composerInfo['name']) 
            || empty($composerInfo['name'])
        ) {
            return $this->error(__('larke-admin::extension.extension_composer_error'));
        }
        
        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9\_\-\/]+$/', $composerInfo['name'])) {
            return $this->error(__('larke-admin::extension.extension_package_error'));
        }
        
        $extensionDirectory = AdminExtension::getExtensionPath('');
        $extensionPath = AdminExtension::getExtensionPath($composerInfo['name']);
        
        $force = $request->input('force');
        
        // 检查扩展目录是否存在
        if (file_exists($extensionPath) && !$force) {
            return $this->error(__('larke-admin::extension.extension_exists', [
                'extension' => $composerInfo['name'],
            ]), \ResponseCode::EXTENSION_EXISTS);
        }
        
        $extensionRemovePath = Str::replaceLast('composer.json', '', $composer[0]['filename']);
        $extensionPregPath = '/^'.str_replace(['\\', '/'], ['\\\\', '\\/'], $extensionRemovePath).'.*?/';
        
        // 解压文件
        $list = $zip->extract(
            PCLZIP_OPT_PATH, $extensionPath,
            PCLZIP_OPT_REMOVE_PATH, $extensionRemovePath,
            PCLZIP_OPT_EXTRACT_DIR_RESTRICTION, $extensionDirectory,
            PCLZIP_OPT_BY_PREG, $extensionPregPath,
            PCLZIP_OPT_REPLACE_NEWER,
        );
        
        if ($list == 0) {
            return $this->error(__('larke-admin::extension.extension_extract_fail', [
                'extension' => $composerInfo['name'],
            ]));
        }
        
        // 覆盖的时候禁用扩展
        if ($force) {
            $installInfo = ExtensionModel::where(['name' => $composerInfo['name']])
                ->first();
            if (! empty($installInfo) && $installInfo['status'] != 0) {
                $installInfo->disable();
            }
        }
        
        // 上传后刷新本地缓存
        AdminExtension::refresh();
        
        return $this->success(__('larke-admin::extension.extension_upload_success', [
                'extension' => $composerInfo['name'],
            ]));
    }
    
    /**
     * 本地扩展注册到composer.json
     *
     * @param string $name
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "仓库注册扩展", 
        desc:  "本地扩展注册到composer.json仓库",
        order: 573,
        auth:  true
    )]
    public function repositoryRegister(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        // 检测本地扩展是否存在
        $extensionDirectory = AdminExtension::checkLocal($name);
        if ($extensionDirectory === false) {
            return $this->error(__('larke-admin::extension.extension_not_local'));
        }
        
        // 扩展本地仓库
        $url = AdminExtension::getExtensionDirectory() . '/' . $name;
        $repository = [
            'type' => 'path',
            'url' => $url,
        ];
        
        $actionStatus = ComposerRepository::create()
            ->withDirectory(base_path())
            ->register($name, $repository);
        if ($actionStatus === false) {
            return $this->error(__('larke-admin::extension.extension_repository_register_fail'));
        }
        
        return $this->success(__('larke-admin::extension.extension_repository_register_success'));
    }
    
    /**
     * 本地扩展从composer.json移除
     *
     * @param string $name
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "仓库移除扩展", 
        desc:  "本地扩展从composer.json仓库移除",
        order: 574,
        auth:  true
    )]
    public function repositoryRemove(string $name, Request $request)
    {
        if (empty($name)) {
            return $this->error(__('larke-admin::extension.extension_package_name_dont_empty'));
        }
        
        // 检测本地扩展是否为本地扩展
        $extensionDirectory = AdminExtension::checkLocal($name);
        if ($extensionDirectory === false) {
            return $this->error(__('larke-admin::extension.extension_not_local'));
        }
        
        $actionStatus = ComposerRepository::create()
            ->withDirectory(base_path())
            ->remove($name);
        if ($actionStatus === false) {
            return $this->error(__('larke-admin::extension.extension_repository_remove_fail'));
        }
        
        return $this->success(__('larke-admin::extension.extension_repository_remove_success'));
    }
    
}
