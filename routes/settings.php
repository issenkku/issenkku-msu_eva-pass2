<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public profile route - ไม่ต้อง login
Route::get('profile/{uuid}', [ProfileController::class, 'showPublic'])->name('profile.public');

Route::middleware('auth:sanctum')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::patch('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('/password/reset/send', [ProfileController::class, 'sendPasswordResetLink'])->name('password.reset.send');

    // Route::get('settings/appearance', function () {
    //     return Inertia::render('settings/Appearance');
    // })->name('appearance');
});
