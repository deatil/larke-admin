<?php

namespace Larke\Admin\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

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
        $this->line("<info>[1]</info> install");
        $this->line("<info>[2]</info> uninstall");
        $this->line("<info>[3]</info> upgrade");
        $this->line("<info>[4]</info> enable");
        $this->line("<info>[5]</info> disable");
        
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
        
        if (!in_array($action, $actions)) {
            $this->line("<error>Enter action is error !</error> ");
            return;
        }
        
        $status = $this->{$action}($name);
        if ($status === false) {
            return;
        }
        
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
        $installInfo = ExtensionModel::where(['name' => $name])
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
        
        $createInfo = ExtensionModel::create([
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

        if (version_compare(Arr::get($installInfo, 'version', 0), Arr::get($info, 'version', 0), '<') == false) {
            $this->line("<error>Extension is not need upgrade !</error> ");
            return false;
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
