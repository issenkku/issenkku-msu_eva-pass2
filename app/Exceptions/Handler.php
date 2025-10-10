<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Auth;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */

    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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

    /**
     * Render an exception into an HTTP response.
     */
    
    public function render($request, Throwable $exception)
    {
        // 🧩 Handle 403 Forbidden responses
        if ($exception instanceof HttpException && $exception->getStatusCode() === 403) {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();

                // Redirect based on user role
                if ($user->hasRole('admin')) {
                    return redirect()->route('dashboard')
                        ->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
                } elseif ($user->hasRole('ผู้บริหาร')) {
                    return redirect()->route('manager.dashboard')
                        ->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
                } elseif ($user->hasRole('กรรมการ')) {
                    return redirect()->route('director.dashboard')
                        ->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
                } elseif ($user->hasRole('ผู้ประเมิน')) {
                    return redirect()->route('evaluator.index')
                        ->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
                } elseif ($user->hasRole('ผู้รับการประเมิน')) {
                    return redirect()->route('evaluatee.dashboard')
                        ->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
                }

                // Default fallback (for users without known roles)
                // return redirect()->route('home')
                //     ->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            }

            // If not logged in, send to login
            return redirect()->route('login')
                ->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        return parent::render($request, $exception);
    }
}


