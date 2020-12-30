<?php

namespace Larke\Admin\Http;

use Illuminate\Http\Exceptions\HttpResponseException;

use Larke\Admin\Contracts\Response as ResponseContract;

/*
 * 响应
 *
 * @create 2020-10-19
 * @author deatil
 */
class Response implements ResponseContract
{
    // 输出头信息列表
    protected $headers = [];
    
    // 跨域
    protected $isAllowOrigin = false;
    
    // 允许跨域域名
    protected $allowOrigin = '*';
    
    // 是否允许后续请求携带认证信息（cookies）, 该值只能是true,否则不返回
    protected $allowCredentials = false; // true or false
    
    // 预检结果缓存时间,缓存
    protected $maxAge = '';
    
    // 该次请求的请求方式
    protected $allowMethods = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
    
    // 该次请求的自定义请求头字段
    protected $allowHeaders = 'X-Requested-With,X_Requested_With,Content-Type';
    
    protected $exposeHeaders = 'Authorization,authenticated';
    
    /**
     * 是否允许跨域域名
     */
    public function withIsAllowOrigin($isAllowOrigin = false)
    {
        if ($isAllowOrigin) {
            $this->isAllowOrigin = true;
        } else {
            $this->isAllowOrigin = false;
        }
        
        return $this;
    }
    
    /**
     * 允许跨域域名
     */
    public function withAllowOrigin($allowOrigin = '*')
    {
        $this->allowOrigin = $allowOrigin;
        
        return $this;
    }
    
    /**
     * 允许后续请求携带认证信息
     */
    public function withAllowCredentials($allowCredentials = false)
    {
        if ($allowCredentials) {
            $this->allowCredentials = true;
        } else {
            $this->allowCredentials = false;
        }
        
        return $this;
    }
    
    /**
     * 预检结果缓存时间
     */
    public function withMaxAge($maxAge = '')
    {
        $this->maxAge = $maxAge;
        
        return $this;
    }
    
    /**
     * 该次请求的请求方式
     */
    public function withAllowMethods($allowMethods = false)
    {
        $this->allowMethods = $allowMethods;
        
        return $this;
    }
    
    /**
     * 该次请求的自定义请求头字段
     */
    public function withAllowHeaders($allowHeaders = false)
    {
        $this->allowHeaders = $allowHeaders;
        
        return $this;
    }
    
    /**
     * 设置 js 允许获取的header字段
     */
    public function withExposeHeaders($exposeHeaders = false)
    {
        $this->exposeHeaders = $exposeHeaders;
        
        return $this;
    }
    
    /**
     * 设置 haders
     */
    public function withHeader($name, $content = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->withHeader($key, $value);
            }
        } else {
            if (!empty($name)) {
                $this->headers[$name] = $content;
            }
        }
        
        return $this;
    }
    
    /**
     * 获取haders
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * 组合跨域 haders
     */
    public function mergeCorsHeaders()
    {
        $header = [];
        if ($this->isAllowOrigin == 1) {
            $header['Access-Control-Allow-Origin']  = $this->allowOrigin;
            $header['Access-Control-Allow-Headers'] = $this->allowHeaders;
            $header['Access-Control-Expose-Headers'] = $this->exposeHeaders;
            $header['Access-Control-Allow-Methods'] = $this->allowMethods;
            
            if ($this->allowCredentials === true) {
                $header['Access-Control-Allow-Credentials'] = $this->allowCredentials;
            }
            
            if (!empty($this->maxAge)) {
                $header['Access-Control-Max-Age'] = $this->maxAge;
            }
        }
        
        $header['Content-Type'] = 'application/json; charset=utf-8';
        
        $this->withHeader($header);
        
        return $this;
    }
    
    /**
     * 输出响应
     * @param boolen $success
     * @param int $code
     * @param string|null $message
     * @param array|null $data
     * @param array $userHeader
     * @return string json
     */
    public function json(
        $success = true, 
        $code = \ResponseCode::INVALID, 
        $message = "", 
        $data = [], 
        $userHeader = []
    ) {
        $result['success'] = $success;
        $result['code'] = $code;
        $message ? $result['message'] = $message : null;
        $data ? $result['data'] = $data : null;
        
        $this->mergeCorsHeaders()->withHeader($userHeader);
        
        $result = json_encode($result, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        
        $header = $this->getHeaders();
        $response = response($result, 200, $header);
        throw new HttpResponseException($response);
    }

}
