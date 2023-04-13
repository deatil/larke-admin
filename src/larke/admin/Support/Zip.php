<?php

declare (strict_types = 1);

namespace Larke\Admin\Support;

use Exception;
use ZipArchive;

/**
 * 压缩处理
 *
 * @create 2023-2-17
 * @author deatil
 */
class Zip
{
    /**
     * @var ZipArchive
     */
    private ZipArchive $archive;

    /**
     * Construct with a given path
     *
     * @param $archive
     */
    public function __construct(ZipArchive $archive = null)
    {
        if (! class_exists('ZipArchive')) {
            throw new Exception('Error: Your PHP version is not compiled with zip support');
        }
        
        $this->archive = $archive ?: new ZipArchive();
    }
    
    /**
     * Open a zip file
     */
    public static function openFile(string $filename): self
    {
        $zip = new self();
        
        $msg = $zip->open($filename);
        if (! empty($msg)) {
            throw new Exception("Error: Failed to open {$filename}! Error: {$msg}");
        }
        
        return $zip;
    }
    
    /**
     * Create a zip file
     */
    public static function createFile(string $filename): self
    {
        $zip = new self();
        
        $msg = $zip->create($filename);
        if (! empty($msg)) {
            throw new Exception("Error: Failed to open {$filename}! Error: {$msg}");
        }
        
        return $zip;
    }
    
    /**
     * Open a zip
     *
     * @param $filename
     * @param $flags
     *
     *  ZipArchive::OVERWRITE
     *  ZipArchive::CREATE
     *  ZipArchive::RDONLY
     *  ZipArchive::EXCL
     *  ZipArchive::CHECKCONS
     */
    public function open(string $filename, int $flags = 0): string
    {
        $res = $this->archive->open($filename, $flags);
        
        if ($res !== true) {
            return $this->getErrorMessage($res);
        }
        
        return "";
    }
    
    /**
     * Create a zip
     *
     * @param $filename
     * @param $flags
     */
    public function create(string $filename, int $flags = ZipArchive::CREATE): string
    {
        $res = $this->archive->open($filename, $flags);
        
        if ($res !== true) {
            return $this->getErrorMessage($res);
        }
        
        return "";
    }

    /**
     * Add an empty directory
     *
     *  @param string $dirname
     *  The directory to add.
     *  @param int $flags
     *  Bitmask consisting of ZipArchive::FL_ENC_GUESS, ZipArchive::FL_ENC_UTF_8, ZipArchive::FL_ENC_CP437. The behaviour of these constants is described on the ZIP constants page.
     */
    public function addEmptyDir(string $dirname, int $flags = 0): void
    {
        $this->archive->addEmptyDir($dirName, $flags);
    }

    /**
     * Add a file to the opened Archive
     *
     *  @param string $filepath 
     *  The path to the file to add.
     *  @param string $entryname
     *  If supplied and not empty, this is the local name inside the ZIP archive that will override the filepath.
     *  @param int $start
     *  For partial copy, start position.
     *  @param int $length
     *  For partial copy, length to be copied, if 0 or -1 the whole file (starting from start) is used.
     *  @param int $flags
     *  Bitmask consisting of ZipArchive::FL_OVERWRITE, ZipArchive::FL_ENC_GUESS, ZipArchive::FL_ENC_UTF_8, ZipArchive::FL_ENC_CP437. The behaviour of these constants is described on the ZIP constants page.
     */
    public function addFile(
        string $filepath,
        string $entryname = "",
        int $start = 0,
        int $length = 0,
        int $flags = ZipArchive::FL_OVERWRITE
    ): void {
        $this->archive->addFile(
            $filepath, 
            $entryname,
            $start,
            $length,
            $flags
        );
    }

    /**
     * Add a file to the opened Archive using its contents
     *
     * @param string $name
     * The name of the entry to create.
     * @param string $content
     * The contents to use to create the entry. It is used in a binary safe mode.
     * @param int $flags
     * Bitmask consisting of ZipArchive::FL_OVERWRITE, ZipArchive::FL_ENC_GUESS, ZipArchive::FL_ENC_UTF_8, ZipArchive::FL_ENC_CP437. The behaviour of these constants is described on the ZIP constants page.
     */
    public function addFromString(string $name, string $content, int $flags = ZipArchive::FL_OVERWRITE): void
    {
        $this->archive->addFromString($name, $content, $flags);
    }

    /**
     * Delete an entry in the archive using its name.
     *
     * @param string $name
     * Name of the entry.
     * @param int $method 
     * ZipArchive::EM_AES_256
     * The encryption method defined by one of the ZipArchive::EM_ constants.
     * @param string|null $password 
     * Optional password, default used when missing.
     */
    public function setEncryptionName(string $name, int $method, ?string $password = null): bool
    {
        return $this->archive->setEncryptionName($name, $method, $password);
    }

    /**
     * Delete an entry in the archive using its name.
     *
     * @param string $name
     */
    public function deleteName(string $name): void
    {
        $this->archive->deleteName($name);
    }

    /**
     * Get the content of a file
     *
     * @return string $name
     * Name of the entry
     * @return int $flags
     * If flags is set to ZipArchive::FL_UNCHANGED, the original unchanged comment is returned.
     */
    public function getCommentName(string $name, int $flags = 0): string
    {
        return $this->archive->getCommentName($name, $flags);
    }

    /**
     * Get the content of a file
     *
     * @return string $name
     * Name of the entry
     * @return string $len
     * The length to be read from the entry. If 0, then the entire entry is read.
     * @return string $flags
     * The flags to use to find the entry. The following values may be ORed.
     *  ZipArchive::FL_UNCHANGED
     *  ZipArchive::FL_COMPRESSED
     *  ZipArchive::FL_NOCASE
     */
    public function getFromName(string $name, int $len = 0, int $flags = 0): string
    {
        return $this->archive->getFromName($name, $len, $flags);
    }

    /**
     * Get the stream of a file
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getStream(string $name): mixed
    {
        return $this->archive->getStream($name);
    }

    /**
     * Extract the complete archive or the given files to the specified destination.
     *
     *  @param string $pathto
     *  Location where to extract the files.
     *  @param array|string|null $files
     *  The entries to extract. It accepts either a single entry name or an array of names.
     *
     * @return bool
     */
    public function extractTo(string $pathto, array|string|null $files = null): bool
    {
        return $this->archive->extractTo($pathto, $files);
    }

    /**
     * Will loop over every item in the archive and will execute the callback on them
     * Will provide the filename for every item
     *
     * @param $callback
     */
    public function each(callable $callback): void
    {
        for ($i = 0; $i < $this->archive->numFiles; ++$i) {
            $stats = $this->archive->statIndex($i);
            if ($stats['size'] === 0 && $stats['crc'] === 0) {
                continue;
            }
            
            call_user_func_array($callback, [
                'file' => $this->archive->getNameIndex($i),
                'stats' => $this->archive->statIndex($i)
            ]);
        }
    }

    /**
     * Checks whether the file is in the archive
     *
     * @param string $name The name of the entry to look up
     * @param name $flags The flags are specified by ORing the following values, or 0 for none of them.
     *  ZipArchive::FL_NOCASE
     *  ZipArchive::FL_NODIR
     *
     * @return bool
     */
    public function fileExists(string $name, int $flags = 0): bool
    {
        return $this->archive->locateName($name, $flags) !== false;
    }

    /**
     * Sets the password to be used for decompressing
     * function named usePassword for clarity
     *
     * @param string $password
     *
     * @return bool
     */
    public function setPassword(string $password): bool
    {
        return $this->archive->setPassword($password);
    }

    /**
     * Returns the status error message, system and/or zip messages.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->archive->getStatusString();
    }

    /**
     * @return ZipArchive
     */
    public function getArchive(): ZipArchive
    {
        return $this->archive;
    }

    /**
     * Closes the archive and saves it
     */
    public function close(): void
    {
        @$this->archive->close();
    }

    /**
     * Get error message
     *
     * @param $code
     * @return string
     */
    private function getErrorMessage($code): string
    {
        return match ($code) {
            ZipArchive::ER_EXISTS => 'ZipArchive::ER_EXISTS - File already exists.',
            ZipArchive::ER_INCONS => 'ZipArchive::ER_INCONS - Zip archive inconsistent.',
            ZipArchive::ER_MEMORY => 'ZipArchive::ER_MEMORY - Malloc failure.',
            ZipArchive::ER_NOENT => 'ZipArchive::ER_NOENT - No such file.',
            ZipArchive::ER_NOZIP => 'ZipArchive::ER_NOZIP - Not a zip archive.',
            ZipArchive::ER_OPEN => 'ZipArchive::ER_OPEN - Can\'t open file.',
            ZipArchive::ER_READ => 'ZipArchive::ER_READ - Read error.',
            ZipArchive::ER_SEEK => 'ZipArchive::ER_SEEK - Seek error.',
            default => "An unknown error [$resultCode] has occurred.",
        };
    }

    /**
     * Dynamically call the default archive instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters): mixed
    {
        return $this->archive->{$method}(...$parameters);
    }
}
