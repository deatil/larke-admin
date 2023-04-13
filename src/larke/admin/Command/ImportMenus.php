<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use Illuminate\Console\Command;

use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Service\Menu as MenuModel;

/**
 * 导入菜单数据
 *
 * > php artisan larke-admin:import-menus --force
 */
class ImportMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:import-menus {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke admin import menus.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rules = AuthRuleModel::where('status', 1)
            ->select([
                'id', 
                'parentid as pid', 
                'title', 
                'url', 
                'method', 
                'slug', 
                'listorder as sort',
                'status', 
            ])
            ->get()
            ->toArray();
        
        $menuModel = new MenuModel();
        
        // 覆盖
        $force = $this->option('force');
        
        $rulesByKey = collect($rules)
            ->keyBy('id')
            ->toArray();
            
        $menus = $menuModel->read();
        $menusByKey = collect($menus)
            ->keyBy('id')
            ->toArray();
        
        if ($force) {
            $menusByKey = array_intersect_key($menusByKey, $rulesByKey);
        }
        
        $newMenus = collect(array_merge($rulesByKey, $menusByKey))
            ->sortBy('slug')
            ->values()
            ->all();
        
        $status = $menuModel->save($newMenus);
        if ($status === false) {
            $this->line('<error>Import menus error.</error>');
            return;
        }
        
        $this->info('Import menus successfully.');
    }
}
