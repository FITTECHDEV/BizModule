<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // Handle CSRF token mismatch
        $this->renderable(function(Exception $exception){
            if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->route('login');
            }
        });

        // Log 404 errors (but exclude source map files and other noise)
        $this->renderable(function (NotFoundHttpException $exception, $request) {
            // Skip logging for source map files and common dev files
            $ignoredExtensions = ['.map', '.map.js', '.js.map', '.css.map'];
            $path = $request->path();

            foreach ($ignoredExtensions as $ext) {
                if (str_ends_with($path, $ext)) {
                    // Return 404 silently without logging
                    return response('', 404);
                }
            }

            // Also ignore common bot/scanner paths
            $ignoredPaths = [
                'favicon.ico',
                'robots.txt',
                'ads.txt',
                '.env',
                'wp-admin',
                'wp-login',
                'phpMyAdmin',
                'phpmyadmin',
            ];

            foreach ($ignoredPaths as $ignoredPath) {
                if (str_contains($path, $ignoredPath)) {
                    return response('', 404);
                }
            }

            // Log legitimate 404 errors
            Log::warning('404 Not Found', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            return response()->view('errors.404', [], 404);
        });
    }
}
