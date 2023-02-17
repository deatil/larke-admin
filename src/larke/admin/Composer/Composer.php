<?php

declare (strict_types = 1);

namespace Larke\Admin\Composer;

use Composer\Autoload\ClassLoader;

use Illuminate\Support\Facades\File;

/**
 * Composer
 *
 * @create 2021-1-10
 * @author deatil
 */
class Composer
{
    /**
     * @var array
     */
    protected static array $files = [];

    /**
     * 解析
     *
     * @param   $path
     * @return  ComposerProperty
     */
    public static function parse(?string $path = null): ComposerProperty
    {
        return new ComposerProperty(static::fromJson($path));
    }

    /**
     * 包用composer安装的版本
     *
     * @param  null|string $packageName
     * @param  null|string $lockFile
     * @return null|string
     */
    public static function getVersion(?string $packageName = null, ?string $lockFile = null): ?string
    {
        if (! $packageName) {
            return null;
        }

        $lockFile = $lockFile ?: base_path('composer.lock');

        $content = collect(static::fromJson($lockFile)['packages'] ?? [])
            ->filter(function ($value) use ($packageName) {
                return $value['name'] == $packageName;
            })->first();

        return $content['version'] ?? null;
    }

    /**
     * 解析 JSON 文件
     *
     * @param   null|string  $path
     * @return  array
     */
    public static function fromJson(?string $path = null): array
    {
        $name = md5($path);
        
        if (isset(static::$files[$name])) {
            return static::$files[$name];
        }

        if (! $path || ! is_file($path)) {
            return static::$files[$name] = [];
        }

        try {
            return static::$files[$name] = (array) json_decode(File::get($path), true);
        } catch (\Throwable $e) {
        }

        return static::$files[$name] = [];
    }
}
