<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Throwable;

final class FriendlyErrorPage
{
    public const SESSION_EXPIRED_MESSAGE = 'เซสชันหมดอายุหรือออกจากระบบแล้ว กรุณาเข้าสู่ระบบอีกครั้ง';

    public const LOGOUT_MESSAGE = 'ออกจากระบบเรียบร้อยแล้ว';

    public static function destinationUrl(?User $user): string
    {
        $routeName = $user?->defaultDashboardRoute();

        if ($routeName !== null && Route::has($routeName)) {
            try {
                return route($routeName);
            } catch (Throwable) {
                // The Login fallback below must remain safe inside error handling.
            }
        }

        return Route::has('login') ? route('login') : '/login';
    }
}
