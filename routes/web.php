<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\SettingsController;



Route::get('/createDepartments', [DepartmentsController::class, 'create'])->name('create'); 
Route::post('/store', [DepartmentsController::class, 'store'])->name('store'); 
Route::put('/departments/{id}', [DepartmentsController::class, 'update'])->name('update');
Route::delete('/departments/{id}', [DepartmentsController::class, 'destroy'])->name('destroy');

Route::get('/settings', [SettingsController::class, 'index'])->name('index');

Route::get('/createSettings', [SettingsController::class, 'create'])->name('create');
Route::post('/store', [SettingsController::class, 'store'])->name('store');