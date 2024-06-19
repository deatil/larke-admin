<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

use Larke\Admin\Model\Admin as AdminModel;

/**
 * 登陆密钥错误
 *
 * @create 2024-6-19
 * @author deatil
 */
class PassportLoginKeyError
{
    /**
     * @var string
     */
    public string $message;
    
    /**
     * 构造方法
     * 
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }
    
}
