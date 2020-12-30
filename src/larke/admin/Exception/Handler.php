<?php

namespace Larke\Admin\Exception;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function render($request, $exception)
    {
        parent::render($request, $exception);
        
        $message = $exception->getMessage();
        
        return response()->json([
            'success' => false,
            'code' => \ResponseCode::INVALID,
            'message' => $message,
        ]);
    }
}
