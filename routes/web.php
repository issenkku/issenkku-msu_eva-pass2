<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Evaluatee\DashboardController;
use App\Http\Controllers\Settings\RoleAndPermissionController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use Inertia\Inertia;
use App\Http\Controllers\Setting\DepartmentsController;
use App\Http\Controllers\Setting\SettingsController;
use App\Http\Controllers\Setting\PositionsController;
use function Pest\Laravel\json;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

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

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
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

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/evaluation/{id}', [DashboardController::class, 'evaluation'])->name('evaluation.show');
Route::put('/evaluation/{id}', [DashboardController::class, 'updateEvaluation'])->name('evaluation.update');

// Additional routes for evaluation system
Route::prefix('evaluation')->name('evaluation.')->group(function () {
    Route::get('/create', [DashboardController::class, 'create'])->name('create');
    Route::post('/store', [DashboardController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [DashboardController::class, 'edit'])->name('edit');
    Route::delete('/{id}', [DashboardController::class, 'destroy'])->name('destroy');
});

Route::view('/criteria', 'criteria_config.index')->name('criteria_config.index');
Route::view('/criteria-config', 'criteria_config.create')->name('criteria_config.create');
require __DIR__.'/report.php';