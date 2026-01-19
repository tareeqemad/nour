<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'operator.approved' => \App\Http\Middleware\EnsureOperatorApproved::class,
            'screen.lock' => \App\Http\Middleware\CheckScreenLock::class,
        ]);
        
        // تحديث نشاط المستخدم عند كل طلب
        $middleware->web(append: [
            \App\Http\Middleware\UpdateUserActivity::class,
            \App\Http\Middleware\CheckScreenLock::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Clean UTF-8 in exception messages to prevent encoding errors
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Clean exception message if it contains UTF-8 issues
            try {
                $message = $e->getMessage();
                if (is_string($message) && !mb_check_encoding($message, 'UTF-8')) {
                    // Message has UTF-8 issues, clean it
                    $cleanedMessage = \App\Providers\AppServiceProvider::cleanStringStatic($message);
                    // Note: We can't modify the exception, but we can log the cleaned version
                    \Log::warning('Exception with UTF-8 issues detected', [
                        'original_message' => substr($message, 0, 100),
                        'cleaned_message' => $cleanedMessage,
                        'exception' => get_class($e),
                    ]);
                }
            } catch (\Throwable $cleanError) {
                // Silently fail to prevent breaking exception handling
                \Log::error('Error cleaning UTF-8 in exception handler', [
                    'error' => $cleanError->getMessage(),
                ]);
            }
            return null; // Let Laravel handle the rest normally
        });
    })->create();
