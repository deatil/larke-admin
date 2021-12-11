<?php

declare (strict_types = 1);

namespace Larke\Admin\Exception;

use Throwable;

use Illuminate\Support\Arr;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

/**
 * 异常返回 json
 *
 * @create 2021-1-13
 * @author deatil
 */
class JsonHandler extends ExceptionHandler
{
    use ResponseJsonTrait;
    
    public function render($request, $exception)
    {
        if ($this->isHttpException($exception)) {
            if ($exception->getStatusCode() != 200) {
                if ($exception->getStatusCode() == 404) {
                    $message = __('没有发现页面');
                } else {
                    $message = $exception->getMessage();
                }
                
                if (config('app.debug')) {
                    $data = [
                        'exception' => $this->renderException($exception),
                    ];
                } else {
                    $data = '';
                }
                
                return $this->error($message, \ResponseCode::EXCEPTION, $data);
            }
        }
        
        return parent::render($request, $exception);
    }

    /**
     * 异常信息
     *
     * @param   Throwable $e
     * @return  array
     */
    protected function renderException(Throwable $e)
    {
        $data = [
            'name' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
            'tables' => [
                'GET Data' => request()->query(),
                'POST Data' => $_POST,
                'Files' => get_included_files(),
                'Cookies' => $_COOKIE,
                'Session' => \Session::all(),
                'Server/Request Data' => request()->server(),
                'Environment Variables' => $_ENV,
            ],
        ];

        return $data;
    }

}
