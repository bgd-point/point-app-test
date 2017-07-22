<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Point\Core\Exceptions\PointException;
use Point\Framework\Exceptions\DomainNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof PointException) {
            return response()->view('core::errors.exceptions', ['messages' => $e->getMessage()]);
        }

        if ($e instanceof TokenMismatchException) {
            return response()->view('core::errors.exceptions', ['messages' => 'Your token expired']);
        }

        if ($e instanceof DomainNotFoundException) {
            \Log::info('domain not found exception from ' . request()->ip());
            return response()->view('framework::errors.domain-not-found-exception');
        }
        
        return parent::render($request, $e);
    }
}
