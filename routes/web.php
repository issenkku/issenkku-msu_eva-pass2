<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DepartmentsController;

Route::get('/', [DepartmentsController::class, 'index'])->name('index');

Route::get('/createDepartments', [DepartmentsController::class, 'create'])->name('create'); 
Route::post('/store', [DepartmentsController::class, 'store'])->name('store'); 

Route::delete('/departments/{id}', [DepartmentsController::class, 'destroy'])->name('destroy');