<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Larke\Admin\Facade\Permission;
use Larke\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;
use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;

/**
 * 重设权限缓存
 *
 * > php artisan larke-admin:reset-permission
 *
 * @create 2021-1-25
 * @author deatil
 */
class ResetPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:reset-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin reset enforcer\'permission.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->runReset();
        
        $this->info('Larke-admin reset permission successfully.');
    }
    
    protected function runReset()
    {
        // 清空原始数据
        $table = config('larkeauth.basic.database.rules_table');
        DB::table($table)->truncate();
        
        // 规则权限
        $rules = AuthRuleAccessModel::with('rule')
            ->whereHas('rule', function($query) {
                $query->where('status', 1);
            })
            ->select()
            ->get()
            ->each(function($data) {
                Permission::addPolicy($data['group_id'], $data['rule']['slug'], strtoupper($data['rule']['method']));
            });
        
        // 分组权限
        $groups = AuthGroupAccessModel::with('group')
            ->whereHas('group', function($query) {
                $query->where('status', 1);
            })
            ->select()
            ->get()
            ->each(function($data) {
                Permission::addRoleForUser($data['admin_id'], $data['group_id']);
            });
    }
}
