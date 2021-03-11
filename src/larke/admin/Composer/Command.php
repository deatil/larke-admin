<?php

declare (strict_types = 1);

namespace Larke\Admin\Composer;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 命令
 *
 * @create 2021-2-20
 * @author deatil
 */
class Command
{
    /**
     * Base path for packages.
     *
     * @var string
     */
    private $basePath;

    /**
     * Memory limit for composer.
     *
     * @var string
     */
    private $memoryLimit = '2048M';

    /**
     * 设置根目录
     *
     * @param string $basePath
     */
    public function withBasePath($basePath = null)
    {
        $this->basePath = $basePath ?? base_path();
        
        return $this;
    }

    /**
     * 设置内存大小
     *
     * @param string $memoryLimit
     */
    public function withMemoryLimit($memoryLimit = null)
    {
        $this->memoryLimit = $memoryLimit;
        
        return $this;
    }

    /**
     * Runs `composer require` command.
     *
     * @param mixed $packages
     * @param array $flags
     *
     * @return void
     */
    public function require($packages, array $flags = [])
    {
        $this->run(
            'require '.implode(' ', Arr::wrap($packages)),
            array_merge(['--update-with-dependencies'], $flags)
        );
    }

    /**
     * Runs `composer update` command.
     *
     * @param mixed $packages
     * @param array $flags
     *
     * @return void
     */
    public function update($packages, array $flags = [])
    {
        $this->run(
            'update '.implode(' ', Arr::wrap($packages)),
            array_merge(['--with-all-dependencies'], $flags)
        );
    }

    /**
     * Runs `composer remove` command.
     *
     * @param mixed $packages
     * @param array $flags
     *
     * @return void
     */
    public function remove($packages, array $flags = [])
    {
        $this->run(
            'remove '.implode(' ', Arr::wrap($packages)),
            array_merge(['--update-with-dependencies'], $flags)
        );
    }

    /**
     * Returns list of installed packages.
     *
     * @return array
     */
    public function installed()
    {
        return $this->all()->toArray();
    }

    /**
     * Returns package version.
     *
     * @param string $package
     *
     * @return string
     */
    public function version($package)
    {
        return $this->standardize(
            $this->all()->get($package)->version
        );
    }

    /**
     * Returns package path.
     *
     * @param string $package
     *
     * @return mixed
     */
    public function path($package)
    {
        return $this->paths()->get($package, null);
    }

    /**
     * Returns package by name.
     *
     * @param string $package
     *
     * @return array
     */
    public function get($package)
    {
        return $this->all()->get($package);
    }

    /**
     * Is package installed?
     *
     * @param string $package
     *
     * @return bool
     */
    public function has($package)
    {
        return $this->all()->has($package);
    }

    /**
     * Returns list of installed packages.
     *
     * @return \Illuminate\Support\Collection
     */
    private function all()
    {
        return Cache::rememberForever('larke-admin.composer-packages', function () {
            $process = $this->process('show', [
                '--direct', 
                '--format=json',
            ]);
            $process->run();

            $output = collect(json_decode($process->getOutput()));

            if ($output->has('installed')) {
                return collect($output->get('installed'))
                    ->keyBy('name');
            }

            return $output;
        });
    }

    /**
     * Returns list of package paths.
     *
     * @return \Illuminate\Support\Collection
     */
    private function paths()
    {
        return Cache::rememberForever('larke-admin.composer-paths', function () {
            $process = $this->process('show', [
                '--direct', 
                '--path', 
                '--format=json',
            ]);
            $process->run();

            $output = collect(json_decode($process->getOutput()));

            if ($output->has('installed')) {
                return collect($output->get('installed'))
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->name => $item->path,
                        ];
                    });
            }

            return $output;
        });
    }

    /**
     * Bust cache.
     *
     * @return void
     */
    private function bustCache()
    {
        Cache::forget('larke-admin.composer-packages');
        Cache::forget('larke-admin.composer-paths');
    }

    /**
     * Run composer process.
     * Log output.
     *
     * @param string $command
     * @param array  $flags
     *
     * @return void
     */
    private function run($command, array $flags = [])
    {
        try {
            $this
                ->process($command, $flags)
                ->mustRun(function ($type, $buffer) {
                    Log::channel('composer')->info($buffer);
                });

            $this->bustCache();
        } catch (ProcessFailedException $exception) {
            Log::error($exception->getMessage(), (array) $exception->getTrace()[0]);
        }
    }

    /**
     * Build new Process Component.
     *
     * @param string $command
     * @param array  $flags
     *
     * @@return \Symfony\Component\Process\Process
     */
    private function process($command, $flags = [])
    {
        $command = array_merge(
            [
                (new PhpExecutableFinder())->find(),
                "-d memory_limit={$this->memoryLimit}",
                exec('which composer'),
                '-vv',
            ],
            explode(' ', $command),
            $flags
        );

        return (new Process($command, $this->basePath))->setTimeout(null);
    }
    

    /**
     * Creates "PHP-standardized" version number
     * (aka Semantic Versioning).
     *
     * @param string $input
     *
     * @return mixed
     */
    public function standardize($input)
    {
        $pattern = "/(\d+)(?:\.(\d+))?(?:\.(\d+))?(.*)?/";
        $output  = [];

        if (preg_match($pattern, $input, $output)) {
            return sprintf(
                '%d.%d.%d%s',
                $output[1] ?? 0,
                $output[2] ?? 0,
                $output[3] ?? 0,
                $output[4] ?? '',
            );
        }

        return $input;
    }

}
