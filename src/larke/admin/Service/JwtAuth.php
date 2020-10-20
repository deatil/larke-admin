<?php

namespace Larke\Admin\Service;

use Larke\Admin\Service\Jwt;

/**
 * jwt验证
 *
 * @create 2020-10-19
 * @author deatil
 */
class JwtAuth extends Jwt
{
    // 单例模式JwtAuth句柄
    private static $instance;

    // 获取JwtAuth的句柄
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // 私有化构造函数
    private function __construct()
    {
        $config = config('larke.jwt');

        $this->withAlg($config['alg']);
        $this->withIss($config['iss']);
        $this->withAud($config['aud']);
        $this->withSub($config['sub']);
        
        $deviceId = request()->get('device_id');
        if (!empty($deviceId)) {
            $this->withJti($deviceId);
        } else {
            $this->withJti($config['jti']);
        }
        $this->withExpTime(intval($config['exptime']));
        $this->withNotBeforeTime($config['notbeforetime']);
        
        $this->withSignerType($config['signer_type']);
        $this->withSecrect($config['secrect']);
        $this->withPrivateKey($config['private_key']);
        $this->withPublicKey($config['public_key']);
    }

    // 私有化clone函数
    private function __clone()
    {}
}
