<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler implements ExceptionHandler
{
    public function report(Throwable $e): void
    {
        // Log the exception
        logger('Throwable', [$e->getMessage(), $e->getTrace()]);
        report($e);
    }

    public function render($request, Throwable $e): \Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson()) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Resource not found.',
                ], 404);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Server error.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        // Fallback for non-JSON (e.g., web)
        return response(view('errors.500'), 500);
    }

    public function shouldReport(Throwable $e): bool
    {
        return true;
    }

    public function renderForConsole($output, Throwable $e): void
    {
        throw $e;
    }
}
