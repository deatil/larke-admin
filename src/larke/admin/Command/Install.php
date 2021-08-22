<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * 安装
 *
 * > php artisan larke-admin:install [--force]
 *
 * @create 2021-1-25
 * @author deatil
 */
class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:install {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin install';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('force')) {
            $this->call('vendor:publish', [
                '--tag' => 'larke-admin-config', 
                '--force' => true,
            ]);
        } else {
            $this->call('vendor:publish', [
                '--tag' => 'larke-admin-config',
            ]);
        }
        
        $this->runSql();
        
        $this->info('Larke-admin install successfully.');
    }
    
    /**
     * Execute Sql.
     *
     * @return mixed
     */
    protected function runSql()
    {
        // 执行数据库
        $installSqlFile = __DIR__.'/../../resources/database/install.sql';
        
        $sqlData = File::get($installSqlFile);
        if (empty($sqlData)) {
            $this->line("<error>Sql file is empty !</error> ");
            return;
        }
        
        $dbPrefix = DB::getConfig('prefix');
        $sqlContent = str_replace('pre__', $dbPrefix, $sqlData);
        
        DB::unprepared($sqlContent);
    }
}
