<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'locale' => SetLocale::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e) {
            if ($e instanceof QueryException || $e instanceof PDOException) {
                $message = $e->getPrevious()?->getMessage() ?? $e->getMessage();

                if (preg_match('/(connection refused|could not connect|access denied|unknown database|connection timed out|cannot connect|server has gone away)/i', $message)) {
                    Log::error('Database connection error: '.$message);

                    return response()->view('errors.database', [], 503);
                }
            }
        });
    })->create();
