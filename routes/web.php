<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Settings\RoleAndPermissionController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use Inertia\Inertia;

Route::get('/', function () {
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

// Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [AuthController::class, 'login']);
Route::middleware('guest')->controller(AuthController::class)->group(function(){
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::resource('/roles', RoleAndPermissionController::class);

// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::get('/profile', function (Request $request) {
//         return response()->json($request->user());
//     });
//     Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
// });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
