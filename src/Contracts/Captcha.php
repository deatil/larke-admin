<?php

declare (strict_types = 1);

namespace Larke\Admin\Contracts;

/*
 * 验证码契约
 *
 * @create 2021-3-15
 * @author deatil
 */
interface Captcha
{
    /**
     * 设置配置
     * 
     * @param array $config 键值对列表
     * 
     * @return object self
     */
    public function withConfig(array $config): self;

    /**
     * 生成验证码信息
     *
     * @return object
     */
    public function makeCode(): self;

    /**
     * 获取验证码
     *
     * @return array
     */
    public function getAttr(): array;

    /**
     * 检查验证码是否正确
     *
     * @param  string $code   需要验证的值
     * @param  string $uniqid 验证码编号
     *
     * @return boolean
     */
    public function check(string $code, string $uniqid): bool;

}
