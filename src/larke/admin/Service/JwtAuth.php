<?php

namespace Larke\Admin\Service;

use Larke\Admin\Jwt;

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
        $configInfo = config('larke');
        $config = array_merge($configInfo, $config);

        $this->withAlg($config['jwt_alg']);
        $this->withIss($config['jwt_iss']);
        $this->withAud($config['jwt_aud']);
        $this->withSub($config['jwt_sub']);
        
        $deviceId = request()->param('device_id');
        if (!empty($deviceId)) {
            $this->withJti($deviceId);
        } else {
            $this->withJti($config['jwt_jti']);
        }
        $this->withExpTime(intval($config['jwt_exptime']));
        $this->withNotBeforeTime($config['jwt_notbeforetime']);
        
        $this->withSignerType($config['jwt_signer_type']);
        $this->withSecrect($config['jwt_secrect']);
        $this->withPrivateKey($config['jwt_private_key']);
        $this->withPublicKey($config['jwt_public_key']);
    }

    // 私有化clone函数
    private function __clone()
    {}
}
