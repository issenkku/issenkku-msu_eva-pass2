<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Settings\RoleAndPermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use Inertia\Inertia;

Route::get('/test', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

Route::get('/roles', [UserController::class, 'getRoles']);

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']);
Route::middleware('guest')->controller(AuthController::class)->group(function(){
    Route::get('/', 'showLoginForm')->name('login');
    Route::post('/', 'login');
});

// routes/web.php (or routes/api.php)
Route::get('/setup-roles-permissions', [RoleAndPermissionController::class, 'setupRolesAndPermissions']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
