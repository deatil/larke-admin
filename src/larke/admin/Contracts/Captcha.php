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
     * @param string|array $name
     * @return string $value
     *
     * @return object
     */
    public function withConfig($name, $value = null);

    /**
     * 生成验证码信息
     *
     * @return object
     */
    public function makeCode();

    /**
     * 获取验证码
     *
     * @return array
     */
    public function getAttr();

    /**
     * 检查验证码是否正确
     *
     * @param string $code 需要验证的值
     * @param string $uniqid 验证码编号
     *
     * @return boolean
     */
    public function check($code, $uniqid = null);

}
