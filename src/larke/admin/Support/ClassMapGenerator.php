<?php

declare (strict_types = 1);

namespace Larke\Admin\Support;

use Symfony\Component\Finder\Finder;

class ClassMapGenerator
{
    /**
     * 创建map列表
     */
    public static function createMap($path, $excluded = null)
    {
        $basePath = $path;
        if (is_string($path)) {
            if (is_file($path)) {
                $path = [new \SplFileInfo($path)];
            } elseif (is_dir($path) || strpos($path, '*') !== false) {
                $path = Finder::create()
                    ->files()
                    ->followLinks()
                    ->name('/\.(php|inc|hh)$/')
                    ->in($path);
            } else {
                return [];
            }
        }

        $map = [];
        $cwd = realpath(getcwd());

        foreach ($path as $file) {
            $filePath = $file->getPathname();
            if (! in_array(pathinfo($filePath, PATHINFO_EXTENSION), ['php', 'inc', 'hh'])) {
                continue;
            }

            if (! self::isAbsolutePath($filePath)) {
                $filePath = $cwd . '/' . $filePath;
                $filePath = self::normalizePath($filePath);
            } else {
                $filePath = preg_replace('{[\\\\/]{2,}}', '/', $filePath);
            }

            $realPath = realpath($filePath);

            if (isset($scannedFiles[$realPath])) {
                continue;
            }

            if ($excluded && preg_match($excluded, strtr($realPath, '\\', '/'))) {
                continue;
            }
            if ($excluded && preg_match($excluded, strtr($filePath, '\\', '/'))) {
                continue;
            }
            
            try {
                $classes = self::findClasses($filePath);
            } catch(\Exception $e) {
                \Log::error($e->getMessage());
                
                continue;
            }
            
            foreach ($classes as $class) {
                if (! isset($map[$class])) {
                    $map[$class] = $filePath;
                }
            }
        }

        return $map;
    }
    
    /**
     * 格式化
     */
    public static function excluded($exclude)
    {
        if (empty($exclude)) {
            return null;
        }
        
        $excluded = '{(' . implode('|', $exclude) . ')}';
        return $excluded;
    }
    
    /**
     * 检测路径
     */
    public static function isAbsolutePath($path)
    {
        return strpos($path, '/') === 0 || substr($path, 1, 1) === ':' || strpos($path, '\\\\') === 0;
    }
    
    /**
     * 规范化路径
     */
    public static function normalizePath($path)
    {
        $parts = [];
        $path = strtr($path, '\\', '/');
        $prefix = '';
        $absolute = false;

        if (preg_match('{^( [0-9a-z]{2,}+: (?: // (?: [a-z]: )? )? | [a-z]: )}ix', $path, $match)) {
            $prefix = $match[1];
            $path = substr($path, \strlen($prefix));
        }

        if (strpos($path, '/') === 0) {
            $absolute = true;
            $path = substr($path, 1);
        }

        $up = false;
        foreach (explode('/', $path) as $chunk) {
            if ('..' === $chunk && ($absolute || $up)) {
                array_pop($parts);
                $up = !(empty($parts) || '..' === end($parts));
            } elseif ('.' !== $chunk && '' !== $chunk) {
                $parts[] = $chunk;
                $up = '..' !== $chunk;
            }
        }

        return $prefix.($absolute ? '/' : '').implode('/', $parts);
    }
    
    /**
     * 从路径找出类
     */
    private static function findClasses($path)
    {
        $extraTypes = PHP_VERSION_ID < 50400 ? '' : '|trait';
        if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.3', '>=')) {
            $extraTypes .= '|enum';
        }

        $contents = @php_strip_whitespace($path);
        if (! $contents) {
            if (!file_exists($path)) {
                $message = 'File at "%s" does not exist, check your classmap definitions';
            } elseif (!is_readable($path)) {
                $message = 'File at "%s" is not readable, check its permissions';
            } elseif ('' === trim(file_get_contents($path))) {
                return [];
            } else {
                $message = 'File at "%s" could not be parsed as PHP, it may be binary or corrupted';
            }
            $error = error_get_last();
            if (isset($error['message'])) {
                $message .= PHP_EOL . 'The following message may be helpful:' . PHP_EOL . $error['message'];
            }
            throw new \RuntimeException(sprintf($message, $path));
        }

        if (!preg_match('{\b(?:class|interface'.$extraTypes.')\s}i', $contents)) {
            return [];
        }

        $contents = preg_replace('{<<<[ \t]*([\'"]?)(\w+)\\1(?:\r\n|\n|\r)(?:.*?)(?:\r\n|\n|\r)(?:\s*)\\2(?=\s+|[;,.)])}s', 'null', $contents);
        $contents = preg_replace('{"[^"\\\\]*+(\\\\.[^"\\\\]*+)*+"|\'[^\'\\\\]*+(\\\\.[^\'\\\\]*+)*+\'}s', 'null', $contents);
        if (strpos($contents, '<?') !== 0) {
            $contents = preg_replace('{^.+?<\?}s', '<?', $contents, 1, $replacements);
            if ($replacements === 0) {
                return [];
            }
        }

        $contents = preg_replace('{\?>(?:[^<]++|<(?!\?))*+<\?}s', '?><?', $contents);

        $pos = strrpos($contents, '?>');
        if (false !== $pos && false === strpos(substr($contents, $pos), '<?')) {
            $contents = substr($contents, 0, $pos);
        }

        if (preg_match('{(<\?)(?!(php|hh))}i', $contents)) {
            $contents = preg_replace('{//.* | /\*(?:[^*]++|\*(?!/))*\*/}x', '', $contents);
        }

        preg_match_all('{
            (?:
                 \b(?<![\$:>])(?P<type>class|interface'.$extraTypes.') \s++ (?P<name>[a-zA-Z_\x7f-\xff:][a-zA-Z0-9_\x7f-\xff:\-]*+)
               | \b(?<![\$:>])(?P<ns>namespace) (?P<nsname>\s++[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\s*+\\\\\s*+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+)*+)? \s*+ [\{;]
            )
        }ix', $contents, $matches);

        $classes = [];
        $namespace = '';

        for ($i = 0, $len = count($matches['type']); $i < $len; $i++) {
            if (!empty($matches['ns'][$i])) {
                $namespace = str_replace([' ', "\t", "\r", "\n"], '', $matches['nsname'][$i]) . '\\';
            } else {
                $name = $matches['name'][$i];

                if ($name === 'extends' || $name === 'implements') {
                    continue;
                }
                if ($name[0] === ':') {
                    $name = 'xhp'.substr(str_replace(['-', ':'], ['_', '__'], $name), 1);
                } elseif ($matches['type'][$i] === 'enum') {
                    $name = rtrim($name, ':');
                }
                $classes[] = ltrim($namespace . $name, '\\');
            }
        }

        return $classes;
    }

}
