<?php

namespace Larke\Admin\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 安装
 *
 * php artisan larke-admin:install [--force]
 *
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
            $this->call('vendor:publish', ['--tag' => 'larke-admin-config', '--force' => true]);
        } else {
            $this->call('vendor:publish', ['--tag' => 'larke-admin-config']);
        }
        
        $this->runSql();
        
        $this->info('Larke-admin install success.');
    }
    
    /**
     * Execute Sql.
     *
     * @return mixed
     */
    protected function runSql()
    {
        // 执行数据库
        $installSqlFile = __DIR__.'/../../resource/database/install.sql';
        $dbPrefix = DB::getConfig('prefix');
        $sqls = file_get_contents($installSqlFile);
        $sqls = str_replace('pre__', $dbPrefix, $sqls);
        DB::unprepared($sqls);
    }
}
