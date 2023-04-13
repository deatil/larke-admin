<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use Illuminate\Console\Command;

/**
 * 清空缓存
 *
 * > php artisan larke-admin:clear-cache
 *
 * @create 2021-1-25
 * @author deatil
 */
class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin clear-cache';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('cache:clear');
        $this->call('view:clear');
        
        // > php artisan route:clear
        // > php artisan config:clear
        // $this->call('route:clear');
        // $this->call('config:clear');
        
        $this->info("Clear cache successfully!");
    }
}
