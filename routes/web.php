<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Settings\RoleAndPermissionController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EvaluatorController;
use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\User\UserController;
use Inertia\Inertia;
use App\Http\Controllers\Setting\DepartmentsController;
use App\Http\Controllers\Setting\SettingsController;
use App\Http\Controllers\Setting\PositionsController;
use App\Http\Controllers\AssignmentDataController;

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
require __DIR__.'/report.php';
