<?php

declare (strict_types = 1);

namespace Larke\Admin\Service;

use Closure;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

/**
 * 上传
 *
 * @create 2020-10-29
 * @author deatil
 */
class Upload
{
    /**
     * 文件夹
     *
     * @var string
     */
    protected $directory = '';

    /**
     * 最后命名
     *
     * @var null
     */
    protected $name = null;
    
    /**
     * 文件系统单例
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $storage = '';

    /**
     * 命名方式 [unique | datetime | sequence]
     *
     * @var bool
     */
    protected $generateName = null;

    /**
     * 权限 [private | public]
     *
     * @var string
     */
    protected $storagePermission;
    
    /**
     * 创建上传
     *
     * @return $this
     */
    public static function create()
    {
        return static::driver(config('larkeadmin.upload.disk'));
    }
    
    /**
     * 使用驱动
     *
     * @param string $disk Disks defined in `config/filesystems.php`.
     * @return $this
     */
    public static function driver(string $disk = null)
    {
        return (new static())->disk($disk);
    }

    /**
     * 默认文件夹
     *
     * @return string
     */
    public function defaultDirectory()
    {
        return config('larkeadmin.upload.directory.file');
    }

    /**
     * 默认驱动
     *
     * @return string
     */
    public function defaultDriver()
    {
        return config('larkeadmin.upload.disk');
    }

    /**
     * 磁盘
     *
     * @param string $disk Disks defined in `config/filesystems.php`.
     * @return $this|bool
     *
     * throw new \Exception
     */
    public function disk(string $disk)
    {
        // 注意此出会抛出 Exception 异常
        $this->storage = Storage::disk($disk);

        return $this;
    }

    /**
     * 根目录
     *
     * @param string $dir
     * @return $this
     */
    public function dir(string $dir)
    {
        if ($dir) {
            $this->directory = $dir;
        }

        return $this;
    }

    /**
     * 自定义命名
     *
     * @param string|callable $name
     * @return $this
     */
    public function name($name)
    {
        if ($name) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * 唯一命名
     *
     * @return $this
     */
    public function uniqueName()
    {
        $this->generateName = 'unique';

        return $this;
    }

    /**
     * 时间命名
     *
     * @return $this
     */
    public function datetimeName()
    {
        $this->generateName = 'datetime';

        return $this;
    }

    /**
     * sequence 命名
     *
     * @return $this
     */
    public function sequenceName()
    {
        $this->generateName = 'sequence';

        return $this;
    }

    /**
     * 驱动
     *
     * @return object
     */
    public function getStorage()
    {
        return $this->storage;
    }
    
    /**
     * 获取最后文件夹
     *
     * @param UploadedFile $file
     * @return string
     */
    public function getStoreName(UploadedFile $file)
    {
        if ($this->generateName == 'unique') {
            return $this->generateUniqueName($file);
        } elseif ($this->generateName == 'datetime') {
            return $this->generateDatetimeName($file);
        } elseif ($this->generateName == 'sequence') {
            return $this->generateSequenceName($file);
        }

        if ($this->name instanceof Closure) {
            return call_user_func_array($this->name, [$this, $file]);
        }

        if (is_string($this->name)) {
            return $this->name;
        }

        return $this->generateClientName($file);
    }

    /**
     * 获取设置的文件夹
     *
     * @return mixed|string
     */
    public function getDirectory()
    {
        if ($this->directory instanceof Closure) {
            return call_user_func($this->directory);
        }

        return $this->directory ?: $this->defaultDirectory();
    }
    
    /**
     * 文件大类
     *
     * @param UploadedFile $file
     * @return mixed|string
     */
    public function getFileType(UploadedFile $file)
    {
        // 扩展名
        $extension = $file->extension();
        
        // 默认类型
        $filetype = 'other';
        
        // 文件类型列表
        $fileTypes = config('larkeadmin.upload.file_types');
        foreach ($fileTypes as $type => $pattern) {
            if (preg_match($pattern, $extension) === 1) {
                $filetype = $type;
                break;
            }
        }
        
        return $filetype;
    }
    
    /**
     * 文件类型
     *
     * @param UploadedFile $file
     * @return mixed|string
     *
     * throw new \Exception
     */
    public function getMimeType(UploadedFile $file)
    {
        // 默认
        $mimeType = $file->getClientMimeType();
        
        // 文件大类
        $filetype = $this->getFileType($file);
        
        // 扩展名
        $extension = $file->extension();
        
        // 视频和音频重新赋值
        if ($filetype == 'video') {
            $mimeType = "video/{$extension}";
        } elseif ($filetype == 'audio') {
            $mimeType = "audio/{$extension}";
        }
        
        return $mimeType;
    }

    /**
     * 上传文件
     *
     * @param UploadedFile $file
     * @return mixed
     *
     * throw new \Exception
     */
    public function upload(UploadedFile $file)
    {
        $this->name = $this->getStoreName($file);
        
        $this->renameIfExists($file);

        if (! is_null($this->storagePermission)) {
            return $this->storage->putFileAs($this->getDirectory(), $file, $this->name, $this->storagePermission);
        }

        return $this->storage->putFileAs($this->getDirectory(), $file, $this->name);
    }

    /**
     * 如果存在重命名
     *
     * @param UploadedFile $file
     * @return void
     */
    public function renameIfExists(UploadedFile $file)
    {
        if ($this->storage->exists("{$this->getDirectory()}/{$this->name}")) {
            $this->name = $this->generateUniqueName($file);
        }
    }

    /**
     * 获取完整路径
     *
     * @param string $path
     * @return string
     */
    protected function fullPath($path)
    {
        if ($this->storage) {
            return $this->storage->path($path);
        }

        return Storage::disk($this->defaultDriver())->path($path);
    }

    /**
     * 访问链接
     *
     * @param string $path
     * @return string
     */
    public function objectUrl(string $path)
    {
        if (URL::isValidUrl($path)) {
            return $path;
        }

        if ($this->storage) {
            return $this->storage->url($path);
        }

        return Storage::disk($this->defaultDriver())->url($path);
    }

    /**
     * 唯一命名
     *
     * @param UploadedFile $file
     * @return string
     */
    public function generateUniqueName(UploadedFile $file)
    {
        return md5(uniqid() . microtime()) . '.' . $file->getClientOriginalExtension();
    }
    
    /**
     * 时间命名
     *
     * @param UploadedFile $file
     * @return string
     */
    public function generateDatetimeName(UploadedFile $file)
    {
        return date('YmdHis').mt_rand(10000, 99999) . '.' . $file->getClientOriginalExtension();
    }

    /**
     * sequence 命名
     *
     * @param UploadedFile $file
     * @return string
     */
    public function generateSequenceName(UploadedFile $file)
    {
        $index = 1;
        $extension = $file->getClientOriginalExtension();
        $original = $file->getClientOriginalName();
        $new = sprintf('%s_%s.%s', $original, $index, $extension);

        while ($this->storage->exists("{$this->getDirectory()}/{$new}")) {
            $index++;
            $new = sprintf('%s_%s.%s', $original, $index, $extension);
        }

        return $new;
    }
    
    /**
     * 原始命名
     *
     * @param UploadedFile $file
     * @return string
     */
    public function generateClientName(UploadedFile $file)
    {
        return $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
    }

    /**
     * 删除文件
     *
     * @param string $path 文件路径
     * @return void
     */
    public function destroy(string $path)
    {
        if ($this->storage->exists($path)) {
            $this->storage->delete($path);
        }
    }

    /**
     * 判断文件是否存在
     *
     * @param string $path 文件路径
     * @return bool
     */
    public function exists(string $path)
    {
        return $this->storage->exists($path);
    }

    /**
     * 设置权限
     *
     * @param string $permission
     * @return $this
     */
    public function storagePermission(string $permission)
    {
        $this->storagePermission = $permission;

        return $this;
    }
    
}
