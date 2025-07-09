<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Evaluatee\DashboardController;
use App\Http\Controllers\Evaluatee\EvaluationScoreController;
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
use function Pest\Laravel\json;

Route::middleware(['auth:sanctum','role:admin'])->group(function () {
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentsController::class, 'index'])->name('index');
        Route::post('/store', [DepartmentsController::class, 'store'])->name('store');
        Route::put('/{id}', [DepartmentsController::class, 'update'])->name('update');
        Route::delete('/{id}', [DepartmentsController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('settings-website')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/store', [SettingsController::class, 'store'])->name('store');
    });

    Route::prefix('positions')->name('positions.')->group(function () {
        Route::get('/', [PositionsController::class, 'index'])->name('index');
        Route::post('/store', [PositionsController::class, 'store'])->name('store');
        Route::put('/{id}', [PositionsController::class, 'update'])->name('update');
        Route::delete('/{id}', [PositionsController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('assignment-data')->name('assignment-data.')->group(function () {
        Route::get('/', [AssignmentDataController::class, 'index'])->name('index');
        Route::get('/create', [AssignmentDataController::class, 'create'])->name('create');
        Route::post('/', [AssignmentDataController::class, 'store'])->name('store');
    });

    Route::resource('/roles', RoleAndPermissionController::class);
});

Route::middleware(['auth:sanctum','role:ผู้ประเมิน'])->group(function () {
    Route::prefix('evaluator-dashboard')->name('evaluator.')->group(function () {
        Route::get('/', [EvaluatorController::class, 'dashboard'])->name('index');
        Route::get('/assignment/{id}', [EvaluatorController::class, 'show'])->name('evaluatee.show');
        Route::get('/assignment/{id}/evaluate', [EvaluatorController::class, 'startEvaluation'])->name('assignment.evaluate');
        Route::get('/assignment/{id}/edit', [EvaluatorController::class, 'edit'])->name('evaluatee.edit');
        Route::put('/assignment/{assignmentDataId}', [EvaluatorController::class, 'update'])->name('evaluatee.update');
        Route::put('/evaluator/{report}/reject', [EvaluatorController::class, 'reject'])->name('reject');
    });
});

Route::middleware(['auth:sanctum','role:ผู้รับการประเมิน'])->group(function () {
    Route::get('/evaluatee-dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/evaluation/{id}', [DashboardController::class, 'evaluation'])->name('evaluation.show');
    Route::post('/evaluation/{id}/scores', [EvaluationScoreController::class, 'storeEvaluationScores'])->name('evaluation_score.store');
});


Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__.'/report.php';