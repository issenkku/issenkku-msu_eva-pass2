<?php

use App\Http\Controllers\AssignmentDataController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Director\DirectorController;
use App\Http\Controllers\Director\DirectorScoreController;
use App\Http\Controllers\Evaluatee\DashboardEvaluateeController;
use App\Http\Controllers\Evaluatee\EvaluateeWorkloadEntryController;
use App\Http\Controllers\Evaluatee\EvaluationScoreController;
use App\Http\Controllers\Evaluatee\EvaluationWorkloadController;
use App\Http\Controllers\EvaluatorController;
use App\Http\Controllers\EvaluatorScoreController;
use App\Http\Controllers\FileExportController;
use App\Http\Controllers\Manager\ManagerController;
use App\Http\Controllers\Manager\ManagerScoreController;
use App\Http\Controllers\QualityScoresController;
use App\Http\Controllers\Setting\DepartmentsController;
use App\Http\Controllers\Setting\JobLevelsController;
use App\Http\Controllers\Setting\PositionsController;
use App\Http\Controllers\Setting\SettingsController;
use App\Http\Controllers\Settings\RoleAndPermissionController;
use App\Http\Controllers\User\ActivityLogController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserTemplateController;
use App\Http\Controllers\Workload\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentsController::class, 'index'])->name('index');
        Route::post('/store', [DepartmentsController::class, 'store'])->name('store');
        Route::post('/reorder', [DepartmentsController::class, 'reorder'])->name('reorder');
        Route::delete('/bulk-destroy', [DepartmentsController::class, 'bulkDestroy'])->name('bulk-destroy');
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
        Route::post('/reorder', [PositionsController::class, 'reorder'])->name('reorder');
        Route::delete('/bulk-destroy', [PositionsController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::put('/{id}', [PositionsController::class, 'update'])->name('update');
        Route::delete('/{id}', [PositionsController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('job-level')->name('job-level.')->group(function () {
        Route::get('/', [JobLevelsController::class, 'index'])->name('index');
        Route::post('/store', [JobLevelsController::class, 'store'])->name('store');
        Route::post('/reorder', [JobLevelsController::class, 'reorder'])->name('reorder');
        Route::delete('/bulk-destroy', [JobLevelsController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::put('/{id}', [JobLevelsController::class, 'update'])->name('update');
        Route::delete('/{id}', [JobLevelsController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/import/template', [UserTemplateController::class, 'download'])->name('import.template');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::post('/import', [UserController::class, 'import'])->name('import');
        Route::delete('/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::patch('/bulk-status', [UserController::class, 'bulkUpdateStatus'])->name('bulk-status');
        Route::put('/{user:id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user:id}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/check-unique', [UserController::class, 'checkUnique'])->name('users.check-unique');
    });

    Route::group(['middleware' => ['auth']], function () {
        Route::get('/user/management/log', [ActivityLogController::class, 'index'])
            ->name('user.management.log');

        Route::get('/user/management/log/{activity}', [ActivityLogController::class, 'show'])
            ->name('user.management.log.show');
    });

    Route::prefix('assignment-data')->name('assignment-data.')->group(function () {
        Route::get('/', [AssignmentDataController::class, 'index'])->name('index');
        Route::get('/create', [AssignmentDataController::class, 'create'])->name('create');
        Route::post('/', [AssignmentDataController::class, 'store'])->name('store');
        Route::get('/{assignmentData}/copy', [AssignmentDataController::class, 'copy'])->name('copy');
        Route::get('/{assignmentData}', [AssignmentDataController::class, 'show'])->name('show');
        Route::get('/{assignmentData}/edit', [AssignmentDataController::class, 'edit'])->name('edit');
        Route::put('/{assignmentData}', [AssignmentDataController::class, 'update'])->name('update');
        Route::delete('/{assignmentData}', [AssignmentDataController::class, 'destroy'])->name('destroy');
    });

    Route::resource('/roles', RoleAndPermissionController::class);

    Route::prefix('quality-scores')->name('quality-scores.')->group(function () {
        Route::get('/', [QualityScoresController::class, 'index'])->name('index');
        Route::get('/create', [QualityScoresController::class, 'create'])->name('create');
        Route::get('/get-criteria-by-report', [QualityScoresController::class, 'getCriteriaByReport'])->name('get-criteria-by-report');
        Route::post('/', [QualityScoresController::class, 'store'])->name('store');
        Route::get('/{qualityScore}', [QualityScoresController::class, 'show'])->name('show');
        Route::get('/{qualityScore}/edit', [QualityScoresController::class, 'edit'])->name('edit');
        Route::put('/{qualityScore}', [QualityScoresController::class, 'update'])->name('update');
        Route::delete('/{qualityScore}', [QualityScoresController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [QualityScoresController::class, 'bulkDestroy'])->name('bulk-destroy');
    });

});

Route::middleware(['auth:sanctum', 'role:ผู้ประเมิน'])->group(function () {
    Route::prefix('evaluator-dashboard')->name('evaluator.')->group(function () {
        Route::get('/', [EvaluatorController::class, 'dashboard'])->name('index');
        Route::get('/evaluator/{id}', [EvaluatorScoreController::class, 'evaluator'])->name('evaluator.show');
        Route::post('/evaluator/{id}/scores', [EvaluatorScoreController::class, 'storeEvaluatorScores'])->name('evaluator_score.store');
    });
});

Route::middleware(['auth:sanctum', 'role:ผู้รับการประเมิน'])->group(function () {
    Route::get('/evaluatee-dashboard', [DashboardEvaluateeController::class, 'index'])->name('evaluatee.dashboard');
    Route::get('/evaluation/{id}', [DashboardEvaluateeController::class, 'evaluation'])->name('evaluation.show');
    Route::post('/evaluation/{id}/scores', [EvaluationScoreController::class, 'storeEvaluationScores'])->name('evaluation_score.store');
    Route::post('/evaluatee/workload-entries', [EvaluateeWorkloadEntryController::class, 'store'])->name('evaluatee.workload-entries.store');
    Route::put('/evaluatee/workload-entries/{id}', [EvaluateeWorkloadEntryController::class, 'update'])->name('evaluatee.workload-entries.update');
    Route::delete('/evaluatee/workload-entries/{id}', [EvaluateeWorkloadEntryController::class, 'destroy'])->name('evaluatee.workload-entries.destroy');
    Route::get('/evaluation-workload', [EvaluationWorkloadController::class, 'index'])->name('evaluatee.workload');
    Route::post('/evaluation-workload/score', [EvaluationWorkloadController::class, 'storeWorkloadScore'])->name('evaluatee.workload-score.store');
    Route::post('/evaluatee/subjects', [SubjectController::class, 'store'])->name('subjects.store.evaluatee');
});

Route::middleware(['auth:sanctum', 'role:กรรมการ'])->group(function () {
    Route::get('/director-dashboard', [DirectorController::class, 'dashboard'])->name('director.dashboard');
    Route::get('/director/{id}', [DirectorScoreController::class, 'director'])->name('director.show');
    Route::post('/director/{id}/scores', [DirectorScoreController::class, 'storeDirectorScores'])->name('director_score.store');
});
Route::middleware(['auth:sanctum', 'role:ผู้บริหาร'])->group(function () {
    Route::get('/manager-dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/manager/{id}', [ManagerScoreController::class, 'manager'])->name('manager.show');
    Route::post('/manager/{id}/scores', [ManagerScoreController::class, 'storeManagerScores'])->name('manager_score.store');
});

Route::middleware(['auth:sanctum', 'role:admin|ผู้บริหาร'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{id}', [DashboardController::class, 'show'])->name('dashboard.show');
    Route::get('/dashboard-data/{id}', [DashboardController::class, 'admin'])->name('admin.show');
});

Route::middleware(['auth:sanctum', 'role:admin|ผู้บริหาร|กรรมการ|ผู้ประเมิน'])->group(function () {
    Route::get('/export/reports', [FileExportController::class, 'exportDashboard'])->name('export.reports');
    Route::get('/admin/export/reports', [FileExportController::class, 'adminExportDashboard'])->name('admin.export.reports');
    Route::get('/reports/{id}/export', [FileExportController::class, 'exportSingleReport'])
        ->name('single.reports.export');
});

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', function (Request $request) {
        $user = $request->user();

        $defaultRoute = $user->defaultDashboardRoute();

        if ($defaultRoute) {
            return redirect()->route($defaultRoute);
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('dashboard'); // ชื่อ route ของ admin dashboard
        }

        if ($user->hasRole('ผู้บริหาร')) {
            return redirect()->route('manager.dashboard'); // ชื่อ route ของ admin dashboard
        }

        if ($user->hasRole('กรรมการ')) {
            return redirect()->route('director.dashboard'); // ชื่อ route ของ admin dashboard
        }

        if ($user->hasRole('ผู้ประเมิน')) {
            return redirect()->route('evaluator.index');
        }

        if ($user->hasRole('ผู้รับการประเมิน')) {
            return redirect()->route('evaluatee.dashboard'); // ชื่อ route ของ evaluatee dashboard
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');

    })->name('home'); // ตั้งชื่อ route นี้ว่า 'home'

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/report.php';
