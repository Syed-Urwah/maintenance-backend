<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'response' => [
                    'response_id' => 1,
                    'response_status' => 404,
                    'response_desc' => "404 Not Found",
                    'exception' => $exception->getMessage()
                ]
            ], 404);
        }

        if ($exception instanceof HttpResponseException && $exception->getStatusCode() == 408) {
            // Handle the 408 Timeout Exception here
            return response()->json(['response' => [
                'response_id' => 1,
                'response_status' => 408,
                'response_desc' =>  'Request timed out',
                'exception' => $exception->getMessage()

            ]], 408);
        }


        return parent::render($request, $exception);
    }
}
