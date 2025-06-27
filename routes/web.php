<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\SettingsController;


Route::prefix('departments')->name('departments.')->group(function () {
    Route::get('/create', [DepartmentsController::class, 'create'])->name('create');   // ฟอร์มเพิ่ม
    Route::post('/', [DepartmentsController::class, 'store'])->name('store');           // บันทึกข้อมูลใหม่
    Route::put('/{id}', [DepartmentsController::class, 'update'])->name('update');      // อัปเดตข้อมูล
    Route::delete('/{id}', [DepartmentsController::class, 'destroy'])->name('destroy'); // ลบข้อมูล
});



// Route::get('/settings', [SettingsController::class, 'index'])->name('index');

// Route::get('/createSettings', [SettingsController::class, 'create'])->name('create');
// // Route::post('/store', [SettingsController::class, 'store'])->name('store');