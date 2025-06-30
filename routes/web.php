<?php

use Illuminate\Support\Facades\Route;
//use Inertia\Inertia;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PositionsController;

Route::prefix('departments')->name('departments.')->group(function () {
    Route::get('/', [DepartmentsController::class, 'index'])->name('index'); // แสดงข้อมูลทั้งหมด
    Route::post('/store', [DepartmentsController::class, 'store'])->name('store');           // บันทึกข้อมูลใหม่
    Route::put('/{id}', [DepartmentsController::class, 'update'])->name('update');      // อัปเดตข้อมูล
    Route::delete('/{id}', [DepartmentsController::class, 'destroy'])->name('destroy'); // ลบข้อมูล
});

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index'); // แสดงข้อมูลทั้งหมด
    Route::post('/store', [SettingsController::class, 'store'])->name('store');
});

Route::prefix('positions')->name('positions.')->group(function () {
    Route::get('/', [PositionsController::class, 'index'])->name('index'); // แสดงข้อมูลทั้งหมด
    Route::post('/store', [PositionsController::class, 'store'])->name('store');           // บันทึกข้อมูลใหม่
    Route::put('/{id}', [PositionsController::class, 'update'])->name('update');      // อัปเดตข้อมูล
    Route::delete('/{id}', [PositionsController::class, 'destroy'])->name('destroy'); // ลบข้อมูล
});

Route::get('/criteria-config', function () {
    return view('criteria_config.index');
});

Route::get('/criteria-configs', function () {
    return view('criteria_config.create');
});

Route::get('/criteria-evaluators', function () {
    return view('criteria_config.evaluators');
});