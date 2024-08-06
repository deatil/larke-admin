<?php

declare (strict_types = 1);

namespace Larke\Admin\Captcha;

use Illuminate\Support\Facades\Cache;

use Larke\Admin\Contracts\Captcha as CaptchaContract;

/**
 * 图形验证码
 *
 * @create 2020-10-25
 * @author deatil
 */
class Captcha implements CaptchaContract
{
    // 图形资源句柄
    protected object $img; 
    
    // 验证码
    protected string $code = ''; 
    
    // 唯一序号
    protected string $uniqid = ''; 
    
    // 设置
    protected array $config = [
        // 随机因子
        'charset' => 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789',
        
        // 验证码长度
        'codelen' => 4,
        
        // 宽度
        'width' => 130,
        
        // 高度
        'height' => 50,
        
        // 字体文件路径
        'font' => '',
        
        // 字体大小
        'fontsize' => 20,
        
        // 验证码缓存时间
        'cachetime' => 300,
    ];
    
    /**
     * 设置验证码
     * 
     * @param  string $code
     * @return object self
     */
    public function withCode(string $code): self
    {
        $this->code = $code;
        
        return $this;
    }
    
    /**
     * 设置唯一序号
     * 
     * @param  string $uniqid
     * @return object self
     */
    public function withUniqid(string $uniqid): self
    {
        $this->uniqid = $uniqid;
        
        return $this;
    }
    
    /**
     * 设置配置
     * 
     * @param array $config 键值对列表
     * 
     * @return object self
     */
    public function withConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        
        return $this;
    }

    /**
     * 生成验证码信息
     *
     * @return object self
     */
    public function makeCode(): self
    {
        // 生成验证码序号
        if (empty($this->uniqid)) {
            $this->uniqid = md5(uniqid('larke.captcha') . mt_rand(10000, 99999));
        }
        
        // 生成验证码字符串
        if (empty($this->code)) {
            $length = strlen($this->config['charset']) - 1;
            
            for ($i = 0; $i < $this->config['codelen']; $i++) {
                $this->code .= $this->config['charset'][mt_rand(0, $length)];
            }
        } else {
            $this->config['codelen'] = strlen($this->code);
        }
        
        // 缓存验证码字符串
        Cache::put($this->uniqid, $this->code, $this->config['cachetime']);
        
        return $this;
    }

    /**
     * 创建验证码图片
     * 
     * @return string
     */
    private function createImage(): string
    {
        // 生成背景
        $this->img = imagecreatetruecolor($this->config['width'], $this->config['height']);
        $color = imagecolorallocate($this->img, mt_rand(220, 255), mt_rand(220, 255), mt_rand(220, 255));
        imagefilledrectangle($this->img, 0, $this->config['height'], $this->config['width'], 0, $color);
        
        // 生成线条
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 50), mt_rand(0, 50), mt_rand(0, 50));
            imageline($this->img, mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), $color);
        }
        
        // 生成雪花
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), '*', $color);
        }
        
        // 生成文字
        $_x = $this->config['width'] / $this->config['codelen'];
        for ($i = 0; $i < $this->config['codelen']; $i++) {
            // 字体颜色
            $fontcolor = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imagettftext($this->img, $this->config['fontsize'], mt_rand(-30, 30), intval($_x * $i + mt_rand(1, 5)), intval($this->config['height'] / 1.4), $fontcolor, $this->config['font'], $this->code[$i]);
        }
        
        ob_start();
        imagepng($this->img);
        $data = ob_get_contents();
        ob_end_clean();
        imagedestroy($this->img);
        
        return base64_encode($data);
    }

    /**
     * 获取验证码数据集合
     *
     * @return array
     */
    public function getAttr(): array
    {
        return [
            'code' => $this->code,
            'uniq' => $this->uniqid,
            'data' => $this->getData(),
        ];
    }

    /**
     * 获取验证码值
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * 获取验证码编号
     *
     * @return string
     */
    public function getUniqid(): string
    {
        return $this->uniqid;
    }

    /**
     * 获取图片内容
     *
     * @return string
     */
    public function getData(): string
    {
        return "data:image/png;base64,{$this->createImage()}";
    }

    /**
     * 检查验证码是否正确
     *
     * @param  string $code   需要验证的值
     * @param  string $uniqid 验证码编号
     *
     * @return boolean
     */
    public function check(string $code, string $uniqid): bool
    {
        if (empty($uniqid)) {
            return false;
        }
        
        // 获取并删除
        $val = Cache::pull($uniqid); 
        
        return is_string($val) && strtolower($val) === strtolower($code);
    }
}
