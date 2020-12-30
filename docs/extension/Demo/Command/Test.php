<?php

namespace Demo\Command;

use Illuminate\Console\Command;

/**
 * Demo
 *
 * php artisan demo:run
 *
 */
class Demo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('demo run success.');
    }
}
