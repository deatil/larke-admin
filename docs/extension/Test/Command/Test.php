<?php

namespace Test\Command;

use Illuminate\Console\Command;

/**
 * test
 *
 * php artisan test:test
 *
 */
class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

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
        $this->info('test run success.');
    }
}
