<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // 正则验证异常
        if ($exception instanceof ValidationException) {
            return response()->json(['code' => '400', 'data' => $exception->getMessage(), 'smg' => '参数错误']);
        }

        // HTTP错误
        if ($exception instanceof HttpException) {
            return response()->json(['code' => $exception->getStatusCode(), 'data' => $exception->getMessage(), 'smg' => 'http错误']);
        }

        // 常规错误
        if ($exception instanceof Exception) {
            return response()->json(['code' => '400', 'data' => $exception->getMessage(), 'smg' => '异常错误']);
        }
        return parent::render($request, $exception);
    }
}
