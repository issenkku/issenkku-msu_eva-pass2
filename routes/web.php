<?php

use App\Http\Controllers\AssignmentDataController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\EvaluatorController;
use App\Http\Controllers\AssignmentsController;

Route::prefix('departments')->name('departments.')->group(function () {
    Route::get('/', [DepartmentsController::class, 'index'])->name('index');
    Route::post('/store', [DepartmentsController::class, 'store'])->name('store');
    Route::put('/{id}', [DepartmentsController::class, 'update'])->name('update');
    Route::delete('/{id}', [DepartmentsController::class, 'destroy'])->name('destroy');
});

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::post('/store', [SettingsController::class, 'store'])->name('store');
});

Route::prefix('positions')->name('positions.')->group(function () {
    Route::get('/', [PositionsController::class, 'index'])->name('index');
    Route::post('/store', [PositionsController::class, 'store'])->name('store');
    Route::put('/{id}', [PositionsController::class, 'update'])->name('update');
    Route::delete('/{id}', [PositionsController::class, 'destroy'])->name('destroy');
});

Route::prefix('assignment-data')->name('assignment-data.')->group(function () {
   Route::get('/', [AssignmentDataController::class, 'index'])->name('index');
   Route::get('/create', [AssignmentDataController::class, 'create'])->name('create');
   Route::post('/', [AssignmentDataController::class, 'store'])->name('store');  // เปลี่ยนจาก '/store' เป็น '/'
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

// ใน web.php - แก้ไขส่วนของ evaluator routes
Route::prefix('evaluator-dashboard')->name('evaluator.')->group(function () {
    // Dashboard หลักของผู้ประเมิน - แสดงรายการ assignments ทั้งหมด
    Route::get('/', [EvaluatorController::class, 'dashboard'])->name('index');
    // Route::get('/evaluations/{id}/edit', [EvaluatorController::class, 'edit'])->name('evaluations.evaluator_form');
    // แสดงรายละเอียด assignment เฉพาะ
    // Route::get('/assignment/{id}', [EvaluatorController::class, 'showAssignment'])->name('assignment.show');

    // // เริ่มการประเมิน
    // Route::get('/assignment/{id}/evaluate', [EvaluatorController::class, 'startEvaluation'])->name('assignment.evaluate');
});
