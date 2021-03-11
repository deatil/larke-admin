<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use Illuminate\Console\Command;

use Larke\Admin\Service\ImportRoute as ImportRouteService;

/**
 * 导入路由信息
 *
 * php artisan larke-admin:import-route
 */
class ImportRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:import-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin import route\'info.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // 导入
            (new ImportRouteService())->import();
        } catch(\Exception $e) {
            $this->line("<error>{$e->getMessage()}!</error> ");
            return;
        }
        
        $this->info('Import route successfully!');
    }
}
