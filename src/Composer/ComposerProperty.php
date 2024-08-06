<?php

declare (strict_types = 1);

namespace Larke\Admin\Composer;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Composer 字段
 *
 * @property string $name
 * @property string $description
 * @property string $type
 * @property array  $keywords
 * @property string $homepage
 * @property string $license
 * @property array  $authors
 * @property array  $require
 * @property array  $require_dev
 * @property array  $suggest
 * @property array  $autoload
 * @property array  $autoload_dev
 * @property array  $scripts
 * @property array  $extra
 * @property string $version
 */
class ComposerProperty implements Arrayable
{
    /**
     * @var array
     */
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * 判断存在
     */
    public function has(string $key): bool
    {
        return Arr::has($this->attributes, $key);
    }

    /**
     * 获取
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * 设置
     */
    public function set(string $key, mixed $val): static
    {
        $new = $this->attributes;

        Arr::set($new, $key, $val);

        return new static($new);
    }

    /**
     * 删除
     */
    public function delete(string $key): static
    {
        $new = $this->attributes;

        Arr::forget($new, $key);

        return new static($new);
    }

    /**
     * 返回数组
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * 返回 json 字符
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * 获取
     */
    public function __get(string $name): mixed
    {
        return $this->get(str_replace('_', '-', $name));
    }
}
