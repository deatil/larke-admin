<?php

declare (strict_types = 1);

namespace Larke\Admin\Http;

use Illuminate\Support\Traits\Macroable;
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
    use Macroable;
    
    // 输出头信息列表
    protected $headers = [];
    
    // 跨域
    protected $isAllowOrigin = false;
    
    // 允许跨域域名
    protected $allowOrigin = '*';
    
    // 是否允许后续请求携带认证信息（cookies
    // 该值只能是 true, 否则不返回，设置为 true | false
    protected $allowCredentials = false; 
    
    // 预检结果缓存时间,缓存
    protected $maxAge = '';
    
    // 该次请求的请求方式
    protected $allowMethods = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
    
    // 该次请求的自定义请求头字段
    protected $allowHeaders = 'X-Requested-With,X_Requested_With,Content-Type';
    
    // js 允许获取的 header 字段
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
     * 设置 js 允许获取的 header 字段
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
            if (! empty($name)) {
                $this->headers[$name] = $content;
            }
        }
        
        return $this;
    }
    
    /**
     * 获取 haders
     */
    public function getHeaders()
    {
        if ($this->isAllowOrigin == 1) {
            return $this->headers;
        }
        
        return [];
    }
    
    /**
     * 组合跨域 headers
     */
    public function mergeCorsHeaders()
    {
        $header = [];
        $header['Access-Control-Allow-Origin']  = $this->allowOrigin;
        $header['Access-Control-Allow-Headers'] = $this->allowHeaders;
        $header['Access-Control-Expose-Headers'] = $this->exposeHeaders;
        $header['Access-Control-Allow-Methods'] = $this->allowMethods;
        
        if ($this->allowCredentials === true) {
            $header['Access-Control-Allow-Credentials'] = "true";
        }
        
        if (! empty($this->maxAge)) {
            $header['Access-Control-Max-Age'] = $this->maxAge;
        }
        
        $this->withHeader($header);
        
        return $this;
    }
    
    /**
     * 输出成功响应
     *
     * @param   int         $code
     * @param   string|null $message
     * @param   array|null  $data
     * @param   array       $userHeader
     * @return  string      json
     */
    public function success(
        $message = "", 
        $data = [], 
        $code = \ResponseCode::SUCCESS, 
        $userHeader = []
    ) {
        return $this->json(true, $code, $message, $data, $userHeader);
    }
    
    /**
     * 输出失败响应
     *
     * @param   string|null   $message
     * @param   int           $code
     * @param   array|null    $data
     * @param   array         $userHeader
     * @return  string        json
     */
    public function error(
        $message = "", 
        $code = \ResponseCode::ERROR, 
        $data = [], 
        $userHeader = []
    ) {
        return $this->json(false, $code, $message, $data, $userHeader);
    }
    
    /**
     * 输出响应
     *
     * @param   boolen      $success
     * @param   int         $code
     * @param   string|null $message
     * @param   array|null  $data
     * @param   array       $userHeader
     * @return  string      json
     */
    public function json(
        $success = true, 
        $code = \ResponseCode::INVALID, 
        $message = "", 
        $data = [], 
        $userHeader = []
    ) {
        $result = [
            'success' => $success,
            'code'    => $code,
        ];
        
        if ($message) {
            $result['message'] = $message;
        }
        if ($data) {
            $result['data'] = $data;
        }
        
        // 返回 JSON 
        $this->returnJson($result, $userHeader);
    }
    
    /**
     * 将数组以标准 json 格式返回
     * 
     * @param   array    $data
     * @param   array    $userHeader
     * @return  string   json
     */
    public function returnJson(array $data, $userHeader = []) 
    {
        $contents = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        
        $this->returnJsonFromString($contents, $userHeader);
    }
    
    /**
     * 将 json 字符串以标准 json 格式返回
     * 
     * @param   string|null  $contents
     * @param   array        $userHeader
     * @return  string       json
     */
    public function returnJsonFromString($contents, $userHeader = []) 
    {
        // 添加 json 输出相应
        $header = array_merge($userHeader, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
        
        $this->returnData($contents, $header);
    }
    
    /**
     * 返回字符
     * 
     * @param   string|null   $contents
     * @param   array         $userHeader
     * @return  string
     */
    public function returnString($contents, $userHeader = []) 
    {
        // 文件输出相应
        $header = array_merge($userHeader, [
            'Content-Type' => 'text/html',
        ]);
        
        $this->returnData($contents, $header);
    }
    
    /**
     * 返回数据
     * 
     * @param   string|null   $contents
     * @param   array         $userHeader
     * @return  string
     */
    public function returnData($contents, $userHeader = []) 
    {
        $this->mergeCorsHeaders()->withHeader($userHeader);
        $header = $this->getHeaders();
        
        $response = response($contents, 200, $header);
        throw new HttpResponseException($response);
    }

}
