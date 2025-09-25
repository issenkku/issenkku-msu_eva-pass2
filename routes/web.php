<?php

use App\Http\Controllers\AssignmentDataController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Director\DirectorController;
use App\Http\Controllers\Director\DirectorScoreController;
use App\Http\Controllers\Evaluatee\DashboardEvaluateeController;
use App\Http\Controllers\Evaluatee\EvaluationScoreController;
use App\Http\Controllers\EvaluatorController;
use App\Http\Controllers\EvaluatorScoreController;
use App\Http\Controllers\FileExportController;
use App\Http\Controllers\Manager\ManagerController;
use App\Http\Controllers\Manager\ManagerScoreController;
use App\Http\Controllers\Setting\DepartmentsController;
use App\Http\Controllers\Setting\PositionsController;
use App\Http\Controllers\Setting\SettingsController;
use App\Http\Controllers\Settings\RoleAndPermissionController;
use App\Http\Controllers\User\ActivityLogController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Exports\ReportsExport;
use App\Exports\SingleReportExport;
use App\Models\Assignments;
use Maatwebsite\Excel\Facades\Excel;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
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
        Route::get('/import/template', [UserController::class, 'downloadTemplate'])->name('import.template');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::post('/import', [UserController::class, 'import'])->name('import');
        Route::put('/{user:id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user:id}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::group(['middleware' => ['auth']], function () {
        // Activity Log Routes
        Route::get('/user/management/log', [ActivityLogController::class, 'index'])
            ->name('user.management.log');
        
        Route::get('/user/management/log/{activity}', [ActivityLogController::class, 'show'])
            ->name('user.management.log.show');
    });

    Route::prefix('assignment-data')->name('assignment-data.')->group(function () {
        Route::get('/', [AssignmentDataController::class, 'index'])->name('index');
        Route::get('/create', [AssignmentDataController::class, 'create'])->name('create');
        Route::post('/', [AssignmentDataController::class, 'store'])->name('store');
        Route::get('/{assignmentData}', [AssignmentDataController::class, 'show'])->name('show');
        Route::get('/{assignmentData}/edit', [AssignmentDataController::class, 'edit'])->name('edit');
        Route::put('/{assignmentData}', [AssignmentDataController::class, 'update'])->name('update');
        Route::delete('/{assignmentData}', [AssignmentDataController::class, 'destroy'])->name('destroy');
    });

    Route::resource('/roles', RoleAndPermissionController::class);

    Route::prefix('quality-scores')->name('quality-scores.')->group(function () {
        Route::get('/', [App\Http\Controllers\QualityScoresController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\QualityScoresController::class, 'create'])->name('create');
        Route::get('/get-criteria-by-report', [App\Http\Controllers\QualityScoresController::class, 'getCriteriaByReport'])->name('get-criteria-by-report');
        Route::post('/', [App\Http\Controllers\QualityScoresController::class, 'store'])->name('store');
        Route::get('/{qualityScore}', [App\Http\Controllers\QualityScoresController::class, 'show'])->name('show');
        Route::get('/{qualityScore}/edit', [App\Http\Controllers\QualityScoresController::class, 'edit'])->name('edit');
        Route::put('/{qualityScore}', [App\Http\Controllers\QualityScoresController::class, 'update'])->name('update');
        Route::delete('/{qualityScore}', [App\Http\Controllers\QualityScoresController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [App\Http\Controllers\QualityScoresController::class, 'bulkDestroy'])->name('bulk-destroy');
    });

});

Route::middleware(['auth:sanctum', 'role:ผู้ประเมิน'])->group(function () {
    Route::prefix('evaluator-dashboard')->name('evaluator.')->group(function () {
        Route::get('/', [EvaluatorController::class, 'dashboard'])->name('index');
        // Route::get('/assignment/{id}', [EvaluatorController::class, 'show'])->name('evaluatee.show');
        // Route::get('/assignment/{id}/evaluate', [EvaluatorController::class, 'startEvaluation'])->name('assignment.evaluate');
        // Route::get('/assignment/{id}/edit', [EvaluatorController::class, 'edit'])->name('evaluatee.edit');
        // Route::put('/assignment/{id}', [EvaluatorController::class, 'update'])->name('evaluatee.update');
        Route::get('/evaluator/{id}', [EvaluatorScoreController::class, 'evaluator'])->name('evaluator.show');
        Route::post('/evaluator/{id}/scores', [EvaluatorScoreController::class, 'storeEvaluatorScores'])->name('evaluator_score.store');
        // Route::put('/evaluator/{report}/reject', [EvaluatorController::class, 'reject'])->name('reject');
    });
});

Route::middleware(['auth:sanctum', 'role:ผู้รับการประเมิน'])->group(function () {
    Route::get('/evaluatee-dashboard', [DashboardEvaluateeController::class, 'index'])->name('evaluatee.dashboard');
    Route::get('/evaluation/{id}', [DashboardEvaluateeController::class, 'evaluation'])->name('evaluation.show');
    Route::post('/evaluation/{id}/scores', [EvaluationScoreController::class, 'storeEvaluationScores'])->name('evaluation_score.store');
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
    // Route::get('/evaluation/{id}', [DashboardEvaluateeController::class, 'evaluation'])->name('evaluation.show');
    Route::get('/dashboard/{id}', [DashboardController::class, 'show'])->name('dashboard.show');
    Route::get('/dashboard-data/{id}', [DashboardController::class, 'admin'])->name('admin.show');
});

Route::middleware(['auth:sanctum', 'role:admin|ผู้บริหาร|กรรมการ|ผู้ประเมิน'])->group(function () {
    // Route::get('/export/reports', function () {
    //     return Excel::download(new ReportsExport(), 'รายงานผลการประเมินโดยรวม.xlsx');
    // })->name('export.reports');
    Route::get('/export/reports', [FileExportController::class, 'exportDashboard'])->name('export.reports');
    Route::get('/admin/export/reports', [FileExportController::class, 'adminExportDashboard'])->name('admin.export.reports');
    Route::get('/reports/{id}/export', [FileExportController::class, 'exportSingleReport'])
    ->name('single.reports.export');
});

Route::middleware('guest')->group(function () {
    // 1. ถ้าเข้า path "/" และยังไม่ล็อกอิน ให้ไปที่หน้า login
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // 2. ย้าย Route ของ AuthController มาไว้ในกลุ่มนี้เพื่อความเป็นระเบียบ
    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // 3. ถ้าเข้า path "/" และล็อกอินแล้ว ให้ redirect ตาม role
    Route::get('/', function (Request $request) {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            // ถ้าเป็น admin หรือ ผู้บริหาร ให้ไปที่ dashboard ของ admin
            return redirect()->route('dashboard'); // ชื่อ route ของ admin dashboard
        }

        if ($user->hasRole('ผู้บริหาร')) {
            // ถ้าเป็น admin หรือ ผู้บริหาร ให้ไปที่ dashboard ของ admin
            return redirect()->route('manager.dashboard'); // ชื่อ route ของ admin dashboard
        }

        if ($user->hasRole('กรรมการ')) {
            // ถ้าเป็น admin หรือ ผู้บริหาร ให้ไปที่ dashboard ของ admin
            return redirect()->route('director.dashboard'); // ชื่อ route ของ admin dashboard
        }

        if ($user->hasRole('ผู้ประเมิน')) {
            // ถ้าเป็นผู้ประเมิน ให้ไปที่ dashboard ของผู้ประเมิน
            return redirect()->route('evaluator.index');
        }

        if ($user->hasRole('ผู้รับการประเมิน')) {
            // ถ้าเป็นผู้รับการประเมิน ให้ไปที่ dashboard ของผู้รับการประเมิน
            return redirect()->route('evaluatee.dashboard'); // ชื่อ route ของ evaluatee dashboard
        }
        // (ทางเลือก) ถ้ามี role อื่นๆ หรือไม่มี role ที่ตรงเงื่อนไขเลย
        // อาจจะ logout แล้ว redirect ไปหน้า login เพื่อความปลอดภัย
        Auth::logout();

        return redirect()->route('login')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');

        return redirect()->route('login')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');

    })->name('home'); // ตั้งชื่อ route นี้ว่า 'home'

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/report.php';