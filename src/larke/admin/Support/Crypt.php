<?php

declare (strict_types = 1);

namespace Larke\Admin\Support;

use phpseclib3\Crypt\AES;

/**
 * 加密/解密
 *
 * @create 2021-1-29
 * @author deatil
 */
class Crypt
{
    /**
     * $var string
     */
    protected $mode = 'cbc';
    
    /**
     * $var string
     */
    protected $iv = 'a91ebd0f3c65209y';
    
    /**
     * 设置mode
     *
     * @param string $mode
     * @return object $this
     */
    public function withMode($mode)
    {
        $this->mode = $mode;
        
        return $this;
    }
    
    /**
     * 设置iv
     *
     * @param string $iv
     * @return object $this
     */
    public function withIv($iv)
    {
        $this->iv = $iv;
        
        return $this;
    }
    
    /**
     * 加密函数
     * @param string $txt 需要加密的字符串
     * @param string $key 密钥
     * @return string 返回加密结果
     */
    public function encrypt($plaintext, $key = '')
    {
        if (empty($plaintext) || empty($key)) {
            return $plaintext;
        }
        
        $plaintext = base64_encode(time() . '_' . $plaintext);
        $aes = new AES($this->mode);
        $aes->setIV($this->iv);
        $aes->setKey($key);
        $encodeDate = $aes->encrypt($plaintext);
        
        return base64_encode($encodeDate);
    }

    /**
     * 解密函数
     * @param string $txt 需要解密的字符串
     * @param string $key 密匙
     * @param string $ttl 过期时间
     * @return string|null 字符串类型的返回结果
     */
    public function decrypt($plaintext, $key = '', $ttl = 0)
    {
        if (empty($plaintext) || empty($key)) {
            return $plaintext;
        }
        
        $aes = new AES($this->mode);
        $aes->setIV($this->iv);
        $aes->setKey($key);
        $decodeDate = $aes->decrypt(base64_decode($plaintext));
        
        $decodeDate = trim(base64_decode($decodeDate));
        if (preg_match("/\d{10}_/s", substr($decodeDate, 0, 11))) {
            if ($ttl > 0 && (time() - substr($decodeDate, 0, 10) > $ttl)) {
                $decodeDate = null;
            } else {
                $decodeDate = substr($decodeDate, 11);
            }
        }
        
        return $decodeDate;
    }
}
