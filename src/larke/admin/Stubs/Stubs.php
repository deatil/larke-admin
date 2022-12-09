<?php

declare (strict_types = 1);

namespace Larke\Admin\Stubs;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

/**
 * 模板生成
 *
 * @create 2022-12-8
 * @author deatil
 */
class Stubs
{
    /**
     * 快捷创建
     */
    public static function create(): static
    {
        return new static();
    }
    
    /**
     * 生成控制器
     */
    public function makeController(string $name, array $data = [], bool $force = false): mixed
    {
        // 当前时间
        $datetime = now()->rawFormat('Y-m-d H:i:s');
        
        $defalut = [
            'datetime' => $datetime,
        ];
        $data = array_merge($defalut, $data);
        
        $srcFile = __DIR__ . '/stubs/Controller.stub';
        $dstFile = app_path("Admin/Http/Controllers/" . $name . "Controller.php");
        
        $status = $this->copyFile($srcFile, $dstFile, $data, $force);
        if ($status !== true) {
            return $status;
        }
        
        $nameController = $name . "Controller";
        
        $routeName = Str::kebab($name);
        
        // 添加控制器
        $routes = <<<EOF
#### 创建控制器[{$nameController}] / {$datetime}

// 管理分组
\$router->get('/{$routeName}', '{$nameController}@index')->name('app-admin.{$routeName}.index');
\$router->get('/{$routeName}/{id}', '{$nameController}@detail')->name('app-admin.{$routeName}.detail');
\$router->post('/{$routeName}', '{$nameController}@create')->name('app-admin.{$routeName}.create');
\$router->put('/{$routeName}/{id}', '{$nameController}@update')->name('app-admin.{$routeName}.update');
\$router->delete('/{$routeName}/{id}', '{$nameController}@delete')->name('app-admin.{$routeName}.delete');
\$router->patch('/{$routeName}/{id}/sort', '{$nameController}@listorder')->name('app-admin.{$routeName}.listorder');
\$router->patch('/{$routeName}/{id}/enable', '{$nameController}@enable')->name('app-admin.{$routeName}.enable');
\$router->patch('/{$routeName}/{id}/disable', '{$nameController}@disable')->name('app-admin.{$routeName}.disable');

EOF;

        $readmeFile = app_path("Admin/README.md");
        if (File::exists($readmeFile)) {
            File::append($readmeFile, $routes);
        }
        
        return true;
    }
    
    /**
     * 生成模型
     */
    public function makeModel(string $file, array $data = [], bool $force = false): mixed
    {
        $defalut = [
            'datetime' => now()->rawFormat('Y-m-d H:i:s'),
        ];
        $data = array_merge($defalut, $data);
        
        $srcFile = __DIR__ . '/stubs/Model.stub';
        $dstFile = app_path("Admin/Models/" . $file . ".php");
        
        return $this->copyFile($srcFile, $dstFile, $data, $force);
    }
    
    /**
     * 生成 app-admin 目录
     */
    public function makeAppAdmin(bool $force = false): mixed
    {
        $directory = __DIR__ . '/stubs/admin';
        $destination = app_path("Admin");

        if (File::exists($destination) && !$force) {
            return "[{$destination}] exists";
        }
        
        return File::copyDirectory($directory, $destination);
    }
    
    /**
     * 生成文件夹
     */
    public function makeDir($path, $mode = 0755, $recursive = false): bool
    {
        return File::ensureDirectoryExists($path, $mode, $recursive);
    }
    
    /**
     * 复制文件
     */
    public function copyFile(string $src, string $dst, array $data = [], bool $force = false): mixed
    {
        if (! File::exists($src)) {
            return "[{$src}] not exists !";
        }
        
        if (File::exists($dst) && !$force) {
            return "[{$dst}] exists !";
        }
        
        $srcData = File::get($src);
        
        $find = [];
        $replace = [];
        
        foreach ($data as $key => $value) {
            $find[] = '{' . $key . '}';
            $replace[] = $value;
        }
        
        // 替换数据
        $srcData = str_replace($find, $replace, $srcData);
        
        if (File::put($dst, $srcData, true)) {
            return true;
        }
        
        return "copy error !";
    }
    
    /**
     * 复制文件夹
     */
    public function copyDirectory(string $directory, string $destination, $options = null): mixed
    {
        if (! File::isDirectory($directory)) {
            return "[{$directory}] not dir";
        }
        
        return File::copyDirectory($directory, $destination, $options);
    }
    
    /**
     * 追加数据到文件
     */
    public function append(string $path, string $data): mixed
    {
        if (!File::exists($path) || !File::isFile($path) || !File::isWritable($path)) {
            return "[{$path}] file not exists";
        }
        
        return File::append($path, $data);
    }
    
    /**
     * 判断文件是否存在
     */
    public function exists(string $path): bool
    {
        return File::exists($path);
    }
}
