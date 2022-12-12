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
     * 生成扩展
     */
    public function makeExtension(string $author, string $name, bool $force = false): mixed
    {
        // 格式化
        $authorName = Str::kebab($author);
        $extensionName = Str::kebab($name);
        $extensionTitle = Str::studly($name);
        $namespace = Str::studly($author) . "\\" . $extensionTitle;
        $composerNamespace = Str::studly($author) . "\\\\" . $extensionTitle;
        
        // 模板路径
        $stubPath = function($path) {
            return __DIR__ . '/stubs/extension/' . $path;
        };

        // 扩展路径
        $extensionPath = function($path) use($authorName, $extensionName) {
            return base_path("extension/{$authorName}/{$extensionName}/{$path}");
        };
        
        $extension = $extensionPath("");
        if (File::exists($extension) && !$force) {
            return "[{$extension}] is exists !";
        }
        
        $data = [
            'datetime' => now()->rawFormat('Y-m-d H:i:s'),
            'authorName' => $authorName,
            'extensionName' => $extensionName,
            'extensionTitle' => $extensionTitle,
            'namespace' => $namespace,
            'composerNamespace' => $composerNamespace,
        ];
        
        $files = [
            'README.md',
            'composer.json',
            'src/Command/Cmd.php',
            'src/Controller/Index.php',
            'src/ServiceProvider.php',
            'resources/assets/.gitkeep',
            'resources/assets/router.js',
            'resources/assets/views/index.vue',
            'resources/assets/lang/zh.js',
            'resources/assets/lang/en.js',
            'resources/assets/api/index.js',
            'resources/route/admin.php',
            'resources/rules/rules.php',
            'logo.png',
            '.gitignore',
            '.gitattributes',
        ];
        
        // 需要替换的文件
        foreach ($files as $file) {
            $this->copyFile($stubPath($file), $extensionPath($file), $data, $force);
        }
        
        return true;
    }
    
    /**
     * 生成文件夹
     */
    public function makeDir($path, $mode = 0755, $recursive = false)
    {
        File::ensureDirectoryExists($path, $mode, $recursive);
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
        
        // 创建文件夹
        $dstDir = dirname($dst);
        $this->makeDir($dstDir, 0755, true);
        
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
    
    /**
     * 复制
     */
    public function copy(string $path, string $target): bool
    {
        return File::copy($path, $target);
    }
}
