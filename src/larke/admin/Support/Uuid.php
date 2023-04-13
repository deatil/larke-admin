<?php

declare (strict_types = 1);

namespace Larke\Admin\Support;

use DateTimeInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;

/**
 * Uuid
 *
 * @create 2022-12-13
 * @author deatil
 */
class Uuid
{
    /**
     * 生成 uuid 字符
     *
     * @return string
     */
    public static function toString(): string
    {
        return static::toV4String();
    }

    /**
     * 生成 v1 字符
     *
     * @return string
     */
    public static function toV1String($node = null, ?int $clockSeq = null): string
    {
        $uuid = RamseyUuid::uuid1($node, $clockSeq);
        
        return $uuid->toString();
    }
    
    /**
     * 生成 v2 字符
     *
     * @return string
     */
    public static function toV2String(
        int $localDomain,
        ?IntegerObject $localIdentifier = null,
        ?Hexadecimal $node = null,
        ?int $clockSeq = null
    ): string {
        $uuid = RamseyUuid::uuid2($localDomain, $localIdentifier, $node, $clockSeq);
        
        return $uuid->toString();
    }
    
    /**
     * 生成 v3 字符
     *
     * @return string
     */
    public static function toV3String($ns, string $name): string
    {
        $uuid = RamseyUuid::uuid3($ns, $name);
        
        return $uuid->toString();
    }
    
    /**
     * 生成 v4 字符
     *
     * @return string
     */
    public static function toV4String(): string
    {
        $uuid = RamseyUuid::uuid4();
        
        return $uuid->toString();
    }
    
    /**
     * 生成 v5 字符
     *
     * @return string
     */
    public static function toV5String($ns, string $name): string
    {
        $uuid = RamseyUuid::uuid5($ns, $name);
        
        return $uuid->toString();
    }
    
    /**
     * 生成 v6 字符
     *
     * @return string
     */
    public static function toV6String(
        ?Hexadecimal $node = null,
        ?int $clockSeq = null
    ): string {
        $uuid = RamseyUuid::uuid6($node, $clockSeq);
        
        return $uuid->toString();
    }
    
    /**
     * 生成 v7 字符
     *
     * @return string
     */
    public static function toV7String(?DateTimeInterface $dateTime = null): string 
    {
        $uuid = RamseyUuid::uuid7($dateTime);
        
        return $uuid->toString();
    }
    
    /**
     * 生成 v8 字符
     *
     * @return string
     */
    public static function toV8String(string $bytes): string 
    {
        $uuid = RamseyUuid::uuid8($bytes);
        
        return $uuid->toString();
    }
}