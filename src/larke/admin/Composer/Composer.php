<?php

declare (strict_types = 1);

namespace Larke\Admin\Composer;

use Composer\Autoload\ClassLoader;

use Illuminate\Support\Facades\File;

class Composer
{
    /**
     * @var array
     */
    protected static $files = [];

    /**
     * @param $path
     *
     * @return ComposerProperty
     */
    public static function parse(?string $path)
    {
        return new ComposerProperty(static::fromJson($path));
    }

    /**
     * @param null|string $packageName
     * @param null|string $lockFile
     *
     * @return null
     */
    public static function getVersion(?string $packageName, ?string $lockFile = null)
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
     * @param null|string $path
     *
     * @return array
     */
    public static function fromJson(?string $path)
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
