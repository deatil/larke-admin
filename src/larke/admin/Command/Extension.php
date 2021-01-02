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

/**
 * 扩展
 *
 * php artisan larke-admin:extension
 *
 */
class Extension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:extension';

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
        $name = $this->ask('Please enter an extension name');
        if (empty($name)) {
            $this->line("<error>Enter extension is empty !</error> ");
            return;
        }
        
        $this->line("<info>Extension action list: </info> ");
        
        $actions = [
            '[1]' => 'Install',
            '[2]' => 'Uninstall',
            '[3]' => 'Upgrade',
            '[4]' => 'Enable',
            '[5]' => 'Disable',
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
        
        $actions = [
            '1' => 'install',
            '2' => 'uninstall',
            '3' => 'upgrade',
            '4' => 'enable',
            '5' => 'disable',
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
     * @param  Request  $request
     * @return Response
     */
    protected function install($name)
    {
        $installInfo = ExtensionModel::where([
                'name' => $name,
            ])
            ->first();
        if (!empty($installInfo)) {
            $this->line("<error>Extension is installed !</error> ");
            return false;
        }
        
        AdminExtension::loadExtension();
        
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            $this->line("<error>Extension info is empty !</error> ");
            return false;
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            $this->line("<error>Extension info is error !</error> ");
            return false;
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            $this->line("<error>Extension'version ({$info['version']}) is error !</error> ");
            return false;
        }
        
        $adminVersion = config('larkeadmin.admin.version');
        
        try {
            $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        } catch(\Exception $e) {
            $this->line("<error>Extension adaptation'version ({$info['adaptation']}) is error !</error> ");
            return false;
        }
        
        if (! $versionCheck) {
            $this->line("<error>Extension adaptation'version is error ! Admin'version is {$adminVersion} !</error> ");
            return false;
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
                
                return false;
            }
        }
        
        $createInfo = ExtensionModel::create([
            'name' => Arr::get($info, 'name'),
            'title' => Arr::get($info, 'title'),
            'description' => Arr::get($info, 'description'),
            'keywords' => json_encode(Arr::get($info, 'keywords')), 
            'homepage' => Arr::get($info, 'homepage'),
            'authors' => json_encode(Arr::get($info, 'authors', [])),
            'version' => Arr::get($info, 'version'),
            'adaptation' => Arr::get($info, 'adaptation'),
            'require' => json_encode(Arr::get($info, 'require', [])),
            'config' => json_encode(Arr::get($info, 'config', [])),
            'class_name' => Arr::get($info, 'class_name'),
        ]);
        if ($createInfo === false) {
            $this->line("<error>Extension install error !</error> ");
            return false;
        }
        
        AdminExtension::getNewClassMethod($createInfo->class_name, 'install');
    }
    
    /**
     * uninstall
     *
     * @param  Request  $request
     * @return Response
     */
    protected function uninstall($name)
    {
        $info = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($info)) {
            $this->line("<error>Extension is not install !</error> ");
            return false;
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            $this->line("<error>Extension uninstall error !</error> ");
            return false;
        }
        
        AdminExtension::loadExtension();
        AdminExtension::getNewClassMethod($info->class_name, 'uninstall');
    }
    
    /**
     * Upgrade
     *
     * @param  Request  $request
     * @return Response
     */
    protected function upgrade($name)
    {
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            $this->line("<error>Extension is not install !</error> ");
            return false;
        }
        
        AdminExtension::loadExtension();
        $info = AdminExtension::getExtension($name);
        if (empty($info)) {
            $this->line("<error>Extension info is empty !</error> ");
            return false;
        }
        
        $checkInfo = AdminExtension::validateInfo($info);
        if (!$checkInfo) {
            $this->line("<error>Extension info is error !</error> ");
            return false;
        }
        
        $adminVersion = config('larkeadmin.admin.version');
        
        try {
            $versionCheck = Semver::satisfies($adminVersion, $info['adaptation']);
        } catch(\Exception $e) {
            $this->line("<error>Extension adaptation'version ({$info['adaptation']}) is error !</error> ");
            return false;
        }
        
        if (! $versionCheck) {
            $this->line("<error>Extension adaptation'version is error ! Admin'version is {$adminVersion} !</error> ");
            return false;
        }
        
        try {
            $infoVersion = (new VersionParser())->normalize($info['version']);
        } catch(\Exception $e) {
            $this->line("<error>Extension'version ({$info['version']}) is error !</error> ");
            return false;
        }
        
        $infoVersion = Arr::get($info, 'version', 0);
        $installVersion = Arr::get($installInfo, 'version', 0);
        if (!Comparator::greaterThan($infoVersion, $installVersion)) {
            $this->line("<error>Extension is not need upgrade !</error> ");
            return false;
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
                
                return false;
            }
        }
        
        $updateInfo = $installInfo->update([
            'name' => Arr::get($info, 'name'),
            'title' => Arr::get($info, 'title'),
            'description' => Arr::get($info, 'description'),
            'keywords' => json_encode(Arr::get($info, 'keywords')), 
            'homepage' => Arr::get($info, 'homepage'),
            'authors' => json_encode(Arr::get($info, 'authors', [])),
            'version' => Arr::get($info, 'version'),
            'adaptation' => Arr::get($info, 'adaptation'),
            'require' => json_encode(Arr::get($info, 'require', [])),
            'config' => json_encode(Arr::get($info, 'config', [])),
            'class_name' => Arr::get($info, 'class_name'),
            'upgradetime' => time(),
        ]);
        if ($updateInfo === false) {
            $this->line("<error>Extension upgrade error !</error> ");
            return false;
        }
        
        AdminExtension::getNewClassMethod(Arr::get($info, 'class_name'), 'upgrade');
    }
    
    /**
     * 启用
     *
     * @param  Request  $request
     * @return Response
     */
    protected function enable($name)
    {
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            $this->line("<error>Extension is not install !</error> ");
            return false;
        }
        
        if ($installInfo['status'] == 1) {
            $this->line("<error>Extension is enableing !</error> ");
            return false;
        }
        
        $status = $installInfo->enable();
        if ($status === false) {
            $this->line("<error>Extension enable error !</error> ");
            return false;
        }
        
        AdminExtension::loadExtension();
        AdminExtension::getNewClassMethod($installInfo->class_name, 'enable');
    }
    
    /**
     * 禁用
     *
     * @param  Request  $request
     * @return Response
     */
    protected function disable($name)
    {
        $installInfo = ExtensionModel::where(['name' => $name])
            ->first();
        if (empty($installInfo)) {
            $this->line("<error>Extension is not install !</error> ");
            return false;
        }
        
        if ($installInfo['status'] == 0) {
            $this->line("<error>Extension is disableing !</error> ");
            return false;
        }
        
        $status = $installInfo->disable();
        if ($status === false) {
            $this->line("<error>Extension disable error !</error> ");
            return false;
        }
        
        AdminExtension::getNewClassMethod($installInfo->class_name, 'disable');
    }
}
