<?php

declare (strict_types = 1);

namespace Larke\Admin\Exception;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * 异常返回json
 *
 * @create 2021-1-13
 * @author deatil
 */
class JsonHandler extends ExceptionHandler
{
    public function render($request, $exception)
    {
        $message = $exception->getMessage();
        if (empty($message)) {
            return parent::render($request, $exception);
        }
        
        parent::render($request, $exception);
        
        return response()->json([
            'success' => false,
            'code' => \ResponseCode::EXCEPTION,
            'message' => $message,
        ]);
    }

}
