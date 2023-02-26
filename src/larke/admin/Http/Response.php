<?php

declare (strict_types = 1);

namespace Larke\Admin\Http;

use Illuminate\Support\Traits\Macroable;

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
    protected array $headers = [];
    
    /**
     * Initializes a new Response
     *
     * @param array $headers
     */
    public function __construct(array $headers = []) 
    {
        $this->headers = array_merge($this->headers, $headers);
    }
    
    /**
     * 允许跨域域名 *
     */
    public function withAllowOrigin(string $allowOrigin): self
    {
        return $this->withHeader('Access-Control-Allow-Origin', $allowOrigin);
    }
    
    /**
     * 允许后续请求携带认证信息
     */
    public function withAllowCredentials(bool $allowCredentials): self
    {
        // 是否允许后续请求携带认证信息(cookies)
        // 该值只能是 true, 否则不返回
        if ($allowCredentials) {
            return $this->withHeader('Access-Control-Allow-Credentials', "true");
        }
        
        return $this;
    }
    
    /**
     * 预检结果缓存时间
     */
    public function withMaxAge(string $maxAge): self
    {
        if (! empty($maxAge)) {
            return $this->withHeader('Access-Control-Max-Age', $maxAge);
        }
        
        return $this;
    }
    
    /**
     * 该次请求的请求方式
     * GET,POST,PATCH,PUT,DELETE,OPTIONS
     */
    public function withAllowMethods(string $allowMethods): self
    {
        return $this->withHeader('Access-Control-Allow-Methods', $allowMethods);
    }
    
    /**
     * 该次请求的自定义请求头字段
     * X-Requested-With,X_Requested_With,Content-Type
     */
    public function withAllowHeaders(string $allowHeaders): self
    {
        return $this->withHeader('Access-Control-Allow-Headers', $allowHeaders);
    }
    
    /**
     * 设置 js 允许获取的 header 字段
     * Authorization,authenticated
     */
    public function withExposeHeaders(string $exposeHeaders): self
    {
        return $this->withHeader('Access-Control-Expose-Headers', $exposeHeaders);
    }
    
    /**
     * 判断 hader
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }
    
    /**
     * 设置 hader
     */
    public function withHeader(string $name, mixed $content): self
    {
        $this->headers[$name] = $content;
        
        return $this;
    }
    
    /**
     * 设置 haders
     */
    public function withHeaders(array $headers): self
    {
        foreach ($headers as $key => $value) {
            $this->withHeader($key, $value);
        }
        
        return $this;
    }
    
    /**
     * 删除 hader
     */
    public function deleteHeader(string $name): void
    {
        unset($this->headers[$name]);
    }
    
    /**
     * 重置 hader
     */
    public function resetHeader(): self
    {
        $this->headers = [];
        
        return $this;
    }
    
    /**
     * 获取 haders
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * 输出成功响应
     *
     * @param  string $message
     * @param  mixed  $data
     * @param  int    $code
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function success(
        string $message = "", 
        mixed  $data    = [], 
        int    $code    = \ResponseCode::SUCCESS, 
        array  $headers = []
    ): mixed {
        return $this->json(true, $code, $message, $data, $headers);
    }
    
    /**
     * 输出失败响应
     *
     * @param  string $message
     * @param  int    $code
     * @param  mixed  $data
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function error(
        string $message = "", 
        int    $code    = \ResponseCode::ERROR, 
        mixed  $data    = [], 
        array  $headers = []
    ): mixed {
        return $this->json(false, $code, $message, $data, $headers);
    }
    
    /**
     * 输出响应
     *
     * @param  boolen $success
     * @param  int    $code
     * @param  string $message
     * @param  mixed  $data
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function json(
        bool   $success = true, 
        int    $code    = \ResponseCode::INVALID, 
        string $message = "", 
        mixed  $data    = [], 
        array  $headers = []
    ): mixed {
        $result = [
            'success' => $success,
            'code'    => $code,
        ];
        
        if (! empty($message)) {
            $result['message'] = $message;
        }
        
        if (! empty($data)) {
            $result['data'] = $data;
        }
        
        // 返回 JSON 
        return $this->returnJson($result, $headers);
    }
    
    /**
     * 将数组以标准 json 格式返回
     * 
     * @param  array $data
     * @param  array $headers
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function returnJson(array $data, array $headers = []): mixed
    {
        $contents = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        
        return $this->returnJsonFromString($contents, $headers);
    }
    
    /**
     * 将 json 字符串以标准 json 格式返回
     * 
     * @param  string $contents
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function returnJsonFromString(string $contents, array $headers = []): mixed
    {
        // 添加 json 输出相应
        $headers = array_merge($headers, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
        
        return $this->returnData($contents, $headers);
    }
    
    /**
     * 返回字符
     * 
     * @param  string $contents
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function returnString(string $contents, array $headers = []): mixed
    {
        // 文件输出相应
        $headers = array_merge($headers, [
            'Content-Type' => 'text/html',
        ]);
        
        return $this->returnData($contents, $headers);
    }
    
    /**
     * 返回数据
     * 
     * @param  string $contents
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function returnData(string $contents, array $headers = []): mixed
    {
        $headers = $this->withHeaders($headers)->getHeaders();
        
        return response($contents, 200, $headers);
    }

}
