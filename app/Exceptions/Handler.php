<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
{
    if ($e instanceof AccessDeniedHttpException) {
        $message = $e->getPrevious() ? $e->getPrevious()->getMessage() : $e->getMessage();
        return response()->json([
            'error' => 'Access denied. Required permission: ' . $message
        ], 403);
    }
    return parent::render($request, $e);
}
}