<?php

namespace Larke\Admin\Service;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Upload
 *
 * @create 2020-10-29
 * @author deatil
 */
class Upload
{
    /**
     * Upload directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * File name.
     *
     * @var null
     */
    protected $name = null;
    
    /**
     * Storage instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $storage = '';

    /**
     * If use unique name to store upload file.
     *
     * @var bool
     */
    protected $useUniqueName = false;
    
    /**
     * If use sequence name to store upload file.
     *
     * @var bool
     */
    protected $useSequenceName = false;

    /**
     * Controls the storage permission. Could be 'private' or 'public'.
     *
     * @var string
     */
    protected $storagePermission;

    /**
     * @var string
     */
    protected $pathColumn;
    
    /**
     * Initialize the storage instance.
     *
     * @return void.
     */
    public function initStorage()
    {
        return $this->disk(config('larke.upload.disk'));
    }

    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('larke.upload.directory.file');
    }

    /**
     * Set disk for storage.
     *
     * @param string $disk Disks defined in `config/filesystems.php`.
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function disk($disk)
    {
        try {
            $this->storage = Storage::disk($disk);
        } catch (\Exception $exception) {
            return false;
        }

        return $this;
    }

    /**
     * Specify the directory upload file.
     *
     * @param string $dir
     *
     * @return $this
     */
    public function dir($dir)
    {
        if ($dir) {
            $this->directory = $dir;
        }

        return $this;
    }

    /**
     * Set name of store name.
     *
     * @param string|callable $name
     *
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
     * Use unique name for store upload file.
     *
     * @return $this
     */
    public function uniqueName()
    {
        $this->useUniqueName = true;

        return $this;
    }

    /**
     * Use sequence name for store upload file.
     *
     * @return $this
     */
    public function sequenceName()
    {
        $this->useSequenceName = true;

        return $this;
    }

    /**
     * Get getStorage.
     *
     * @return object
     */
    public function getStorage()
    {
        return $this->storage;
    }
    
    /**
     * Get store name of upload file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    public function getStoreName(UploadedFile $file)
    {
        if ($this->useUniqueName) {
            return $this->generateUniqueName($file);
        }

        if ($this->useSequenceName) {
            return $this->generateSequenceName($file);
        }

        if ($this->name instanceof \Closure) {
            return $this->name->call($this, $file);
        }

        if (is_string($this->name)) {
            return $this->name;
        }

        return $file->getClientOriginalName();
    }

    /**
     * Get directory for store file.
     *
     * @return mixed|string
     */
    public function getDirectory()
    {
        if ($this->directory instanceof \Closure) {
            return call_user_func($this->directory);
        }

        return $this->directory ?: $this->defaultDirectory();
    }
    
    /**
     * Get mimeType for store file.
     *
     * @return mixed|string
     */
    public function getFileType(UploadedFile $file)
    {
        // 扩展名
        $extension = $file->extension();
        
        $filetype = 'other';
        foreach (config('larke.upload.file_types') as $type => $pattern) {
            if (preg_match($pattern, $extension) === 1) {
                $filetype = $type;
                break;
            }
        }
        
        return $filetype;
    }
    
    /**
     * Get mimeType for store file.
     *
     * @return mixed|string
     */
    public function getMimeType(UploadedFile $file)
    {
        $mimeType = $file->getClientMimeType();
        
        $filetype = $this->getFileType($file);
        
        if ($filetype == 'video') {
            $mimeType = "video/{$extension}";
        }

        if ($filetype == 'audio') {
            $mimeType = "audio/{$extension}";
        }
        
        return $mimeType;
    }

    /**
     * Set path column in has-many related model.
     *
     * @param string $column
     *
     * @return $this
     */
    public function pathColumn($column = 'path')
    {
        $this->pathColumn = $column;

        return $this;
    }

    /**
     * Upload file and delete original file.
     *
     * @param UploadedFile $file
     *
     * @return mixed
     */
    public function upload(UploadedFile $file)
    {
        $this->name = $this->getStoreName($file);
        
        $this->renameIfExists($file);

        if (!is_null($this->storagePermission)) {
            return $this->storage->putFileAs($this->getDirectory(), $file, $this->name, $this->storagePermission);
        }

        return $this->storage->putFileAs($this->getDirectory(), $file, $this->name);
    }

    /**
     * If name already exists, rename it.
     *
     * @param $file
     *
     * @return void
     */
    public function renameIfExists(UploadedFile $file)
    {
        if ($this->storage->exists("{$this->getDirectory()}/$this->name")) {
            $this->name = $this->generateUniqueName($file);
        }
    }

    /**
     * Get file visit url.
     *
     * @param $path
     *
     * @return string
     */
    public function objectUrl($path)
    {
        if ($this->pathColumn && is_array($path)) {
            $path = Arr::get($path, $this->pathColumn);
        }

        if (URL::isValidUrl($path)) {
            return $path;
        }

        if ($this->storage) {
            return $this->storage->url($path);
        }

        return Storage::disk(config('larke.upload.disk'))->url($path);
    }

    /**
     * Generate a unique name for uploaded file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    public function generateUniqueName(UploadedFile $file)
    {
        return md5(uniqid()).'.'.$file->getClientOriginalExtension();
    }

    /**
     * Generate a sequence name for uploaded file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    public function generateSequenceName(UploadedFile $file)
    {
        $index = 1;
        $extension = $file->getClientOriginalExtension();
        $original = $file->getClientOriginalName();
        $new = sprintf('%s_%s.%s', $original, $index, $extension);

        while ($this->storage->exists("{$this->getDirectory()}/$new")) {
            $index++;
            $new = sprintf('%s_%s.%s', $original, $index, $extension);
        }

        return $new;
    }

    /**
     * Destroy original files.
     *
     * @return void.
     */
    public function destroy($path)
    {
        if ($this->storage->exists($path)) {
            $this->storage->delete($path);
        }
    }

    /**
     * Set file permission when stored into storage.
     *
     * @param string $permission
     *
     * @return $this
     */
    public function storagePermission($permission)
    {
        $this->storagePermission = $permission;

        return $this;
    }
    
}
