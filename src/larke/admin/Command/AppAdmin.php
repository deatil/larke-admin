<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

use Larke\Admin\Stubs\Stubs;

/**
 * app-admin 相关
 *
 * > php artisan larke-admin:app-admin create_controller --name=NewsContent [--force]
 * > php artisan larke-admin:app-admin create_model --name=NewsContent [--force]
 * > php artisan larke-admin:app-admin create_app_admin [--force]
 *
 * @create 2022-12-8
 * @author deatil
 */
class AppAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:app-admin
        {type : Run type name.}
        {--name=none : File name.}
        {--force : Force action.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin app-admin action.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');
        if (empty($type)) {
            $this->line("<error>Enter type'name is empty !</error> ");
            return;
        }
        
        switch ($type) {
            case 'create_controller':
                $this->makeController();
                
                break;
            case 'create_model':
                $this->makeModel();
                
                break;
            case 'create_app_admin':
                $this->makeAppAdmin();
                
                break;
            default:
                $this->line("<error>Enter type'name is error !</error> ");
                return;
        }
    }

    /**
     * 生成控制器
     */
    public function makeController()
    {
        $name = $this->option('name');
        if (empty($name)) {
            $this->line("<error>Enter file'name is empty !</error> ");
            return;
        }

        $force = $this->option('force');
        
        $data = [
            'controllerName' => $name,
            'controllerPath' => Str::kebab($name),
        ];
        
        $status = Stubs::create()->makeController($name, $data, $force);
        if ($status !== true) {
            $this->line("<error>Make controller fail ! {$status} </error> ");

            return;
        }
        
        $this->info('Make controller successfully!');
    }

    /**
     * 生成模型
     */
    public function makeModel()
    {
        $name = $this->option('name');
        if (empty($name)) {
            $this->line("<error>Enter file'name is empty !</error> ");
            return;
        }
        
        $force = $this->option('force');
        
        $data = [
            'modelName' => $name,
            'modelNameTable' => Str::snake($name),
        ];
        
        $status = Stubs::create()->makeModel($name, $data, $force );
        if ($status !== true) {
            $this->line("<error>Make model fail ! {$status} </error> ");

            return;
        }
        
        $this->info('Make model successfully!');
    }

    /**
     * 生成 app-admin 目录
     */
    public function makeAppAdmin()
    {
        $force = $this->option('force');

        $status = Stubs::create()->makeAppAdmin($force);
        if ($status !== true) {
            $this->line("<error>Make appAdmin dir fail ! {$status} </error> ");

            return;
        }
        
        $this->info('Action appAdmin dir successfully!');
    }
    
    
}
