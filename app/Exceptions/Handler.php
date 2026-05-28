<?php

namespace App\Exceptions;

use App\Models\User;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
    ];

    /**
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
    ];

    /**
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {});
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpException && $exception->getStatusCode() === 403) {
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();
                $errorMessage = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';

                if ($user->hasRole('admin')) {
                    return redirect()->route('dashboard')->with('error', $errorMessage);
                }

                if ($user->hasRole('ผู้บริหาร')) {
                    return redirect()->route('manager.dashboard')->with('error', $errorMessage);
                }

                if ($user->hasRole('กรรมการ')) {
                    return redirect()->route('director.dashboard')->with('error', $errorMessage);
                }

                if ($user->hasRole('ผู้ประเมิน')) {
                    return redirect()->route('evaluator.index')->with('error', $errorMessage);
                }

                if ($user->hasRole('ผู้รับการประเมิน')) {
                    return redirect()->route('evaluatee.dashboard')->with('error', $errorMessage);
                }
            }

            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        return parent::render($request, $exception);
    }
}
