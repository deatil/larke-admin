<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use Composer\Semver\Semver;
use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

use Larke\Admin\Facade\Extension as AdminExtension;
use Larke\Admin\Model\Extension as ExtensionModel;

use function Larke\Admin\do_action;

/**
 * 扩展
 *
 * > php artisan larke-admin:extension [--package=package_name] [--action=install]
 *
 * @create 2021-1-25
 * @author deatil
 */
class Extension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:extension
        {--p|package= : Extension package name.}
        {--a|action= : Extension action.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin extension tool. Default action is "install".';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->option('package');
        if (empty($name)) {
            $name = $this->ask('Please enter an extension name');
            if (empty($name)) {
                $this->line("<error>Enter extension is empty !</error> ");
                return;
            }
        }
        
        $action = $this->option('action');
        if (empty($action)) {
            $this->line("<info>Extension action list: </info> ");
            
            $actions = [
                '[1]' => 'Install',
                '[2]' => 'Uninstall',
                '[3]' => 'Upgrade',
                '[4]' => 'Enable',
                '[5]' => 'Disable',
                '[6]' => 'State',
            ];
            $headers = ['No.', 'Action'];
            $rows = [];
            foreach ($actions as $no => $action) {
                $rows[] = [$no, $action];
            }
            $this->table($headers, $rows, 'default');
            
            $action = $this->ask('Please enter an action or action line');
            if (empty($action)) {
                $action = 'install';
            }
        }
        
        $actions = [
            '1' => 'install',
            '2' => 'uninstall',
            '3' => 'upgrade',
            '4' => 'enable',
            '5' => 'disable',
            '6' => 'state',
        ];
        
        if (isset($actions[$action])) {
            $action = $actions[$action];
        }
        
        if (! in_array($action, $actions)) {
            $this->line("<error>Enter action '{$action}' is error !</error> ");
            return;
        }
        
        $status = $this->{$action}($name);
        if ($status === false) {
            return;
        }
        
        Cache::flush();
        
        $this->info("Extension {$action} run successfully.");
    }
    
    /**
     * install
     *
     * @param  string $name
     * @return void
     */
    protected function install($name)
    {
        $installInfo = ExtensionModel::where([
                'name' => $name,
            ])
            ->first();
        if (!empty($installInfo)) {
            $this->line("<error>Extension is installed !</error> ");
            return ;
        }
        
        AdminExtension::loadExtension();
        
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            $this->line("<error>Extension info is empty !</error> ");
            return ;
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            $this->line("<error>Extension info is error !</error> ");
            return ;
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            $this->line("<error>Extension'version ({$info['version']}) is error !</error> ");
            return ;
        }
        
        $adminVersion = config('larkeadmin.admin.version');
        
        try {
            $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        } catch(\Exception $e) {
            $this->line("<error>Extension adaptation'version ({$info['adaptation']}) is error !</error> ");
            return ;
        }
        
        if (! $versionCheck) {
            $this->line("<error>Extension adaptation'version is error ! Admin'version is {$adminVersion} !</error> ");
            return ;
        }
        
        $requireExtensions = ExtensionModel::checkRequireExtension($info['require']);
        if (!empty($requireExtensions)) {
            $match = collect($requireExtensions)->contains(function ($data) {
                return ($data['match'] === false);
            });
            if ($match) {
                $this->line("<error>Error ! </error>You need check {$name} require'extensions: ");
                
                $headers = ['Name', 'Version', 'InstallVersion', 'Match'];
                $rows = [];
                foreach ($requireExtensions as $requireExtension) {
                    $rows[] = [
                        $requireExtension['name'], 
                        $requireExtension['version'], 
                        $requireExtension['install_version'] ?: '-', 
                        ($requireExtension['match'] == 1) ? 'Yes' : 'No', 
                    ];
                }
                
                $this->table($headers, $rows, 'default');
                
                return ;
            }
        }
        
        AdminExtension::callClassMethod($info['class_name'], 'action');
        
        // 安装前
        do_action('install_extension', $name);
        
        // 安装当前扩展时
        do_action('install_' . $name);

        $createInfo = ExtensionModel::create([
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
            'listorder'   => Arr::get($info, 'order'),
        ]);
        if ($createInfo === false) {
            $this->line("<error>Extension install error !</error> ");
            return ;
        }
        
        // 安装后
        do_action('installed_extension', $name);
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
    }
    
    /**
     * uninstall
     *
     * @param  string $name
     * @return void
     */
    protected function uninstall($name)
    {
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            $this->line("<error>Extension is not install !</error> ");
            return ;
        }
        
        if ($info->status == 1) {
            $this->line("<error>Extension need disable before uninstall !</error> ");
            return ;
        }
        
        AdminExtension::loadExtension();
        AdminExtension::callClassMethod($info['class_name'], 'action');
        
        // 卸载前
        do_action('uninstall_extension', $name);

        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            $this->line("<error>Extension uninstall error !</error> ");
            return ;
        }
        
        // 卸载当前扩展时
        do_action('uninstall_' . $name);
        
        // 卸载后
        do_action('uninstalled_extension', $name);
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
    }
    
    /**
     * Upgrade
     *
     * @param  string $name
     * @return void
     */
    protected function upgrade($name)
    {
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            $this->line("<error>Extension is not install !</error> ");
            return ;
        }
        
        if ($installInfo->status == 1) {
            $this->line("<error>Extension need disable before upgrade !</error> ");
            return ;
        }
        
        AdminExtension::loadExtension();
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            $this->line("<error>Extension info is empty !</error> ");
            return ;
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            $this->line("<error>Extension info is error !</error> ");
            return ;
        }
        
        $adminVersion = config('larkeadmin.admin.version');
        
        try {
            $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        } catch(\Exception $e) {
            $this->line("<error>Extension adaptation'version ({$info['adaptation']}) is error !</error> ");
            return ;
        }
        
        if (! $versionCheck) {
            $this->line("<error>Extension adaptation'version is error ! Admin'version is {$adminVersion} !</error> ");
            return ;
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            $this->line("<error>Extension'version ({$info['version']}) is error !</error> ");
            return ;
        }
        
        $infoVersion = Arr::get($info, 'version', 0);
        $installVersion = Arr::get($installInfo, 'version', 0);
        if (!Comparator::greaterThan($infoVersion, $installVersion)) {
            $this->line("<error>Extension is not need upgrade !</error> ");
            return ;
        }
        
        $requireExtensions = ExtensionModel::checkRequireExtension($info['require']);
        if (!empty($requireExtensions)) {
            $match = collect($requireExtensions)->contains(function ($data) {
                return ($data['match'] === false);
            });
            if ($match) {
                $this->line("<error>Error ! </error>You need check {$name} require'extensions: ");
                
                $headers = ['Name', 'Version', 'InstallVersion', 'Match'];
                $rows = [];
                foreach ($requireExtensions as $requireExtension) {
                    $rows[] = [
                        $requireExtension['name'], 
                        $requireExtension['version'], 
                        $requireExtension['install_version'] ?: '-', 
                        ($requireExtension['match'] == 1) ? 'Yes' : 'No', 
                    ];
                }
                
                $this->table($headers, $rows, 'default');
                
                return ;
            }
        }
        
        AdminExtension::callClassMethod($info['class_name'], 'action');
        
        // 更新前
        do_action('upgrade_extension', $name);
        
        // 更新当前扩展时
        do_action('upgrade_' . $name);

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
            'listorder'   => Arr::get($info, 'order'),
            'upgradetime' => time(),
        ]);
        if ($updateInfo === false) {
            $this->line("<error>Extension upgrade error !</error> ");
            return ;
        }
        
        // 更新后
        do_action('upgraded_extension', $name);

        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
    }
    
    /**
     * 启用
     *
     * @param  string $name
     * @return void
     */
    protected function enable($name)
    {
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            $this->line("<error>Extension is not install !</error> ");
            return ;
        }
        
        if ($installInfo['status'] == 1) {
            $this->line("<error>Extension is enableing !</error> ");
            return ;
        }
        
        AdminExtension::loadExtension();
        AdminExtension::callClassMethod($installInfo['class_name'], 'action');
        
        // 启用前
        do_action('enable_extension', $name);

        $status = $installInfo->enable();
        if ($status === false) {
            $this->line("<error>Extension enable error !</error> ");
            return ;
        }
        
        // 启用当前扩展时
        do_action('enable_' . $name);
        
        // 启用后
        do_action('enabled_extension', $name);
        
        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
    }
    
    /**
     * 禁用
     *
     * @param  string $name
     * @return void
     */
    protected function disable($name)
    {
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            $this->line("<error>Extension is not install !</error> ");
            return ;
        }
        
        if ($installInfo['status'] == 0) {
            $this->line("<error>Extension is disableing !</error> ");
            return ;
        }
        
        AdminExtension::callClassMethod($installInfo['class_name'], 'action');
        
        // 禁用前
        do_action('disable_extension', $name);

        $status = $installInfo->disable();
        if ($status === false) {
            $this->line("<error>Extension disable error !</error> ");
            return ;
        }
        
        // 禁用当前扩展时
        do_action('disable_' . $name);
        
        // 禁用后
        do_action('disabled_extension', $name);

        // 清除缓存
        AdminExtension::forgetExtensionCache($name);
    }
    
    /**
     * 查看扩展状态
     *
     * @param  string $name
     * @return void
     */
    protected function state($name)
    {
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            $this->line("<error>[{$name}] not install</error> ");
            return ;
        } else {
            if ($installInfo['status'] == 0) {
                $this->line("<error>[{$name}] installed and disabled</error> ");
                return ;
            } else {
                $this->line("<info>[{$name}] installed and enabled</info> ");
                return ;
            }
        }
    }

}
