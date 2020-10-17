<?php

namespace Larke\Admin\Command;

use Illuminate\Console\Command;

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
        $this->call('vendor:publish', ['--tag' => 'larke-admin', '--force' => true]);

        if ($this->option('force')) {
            
        }

        $this->info('Larke-admin install success.');
    }
}
