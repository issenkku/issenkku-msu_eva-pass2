<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportStructureController;
use App\Http\Controllers\Workload\SubjectController;
use App\Http\Controllers\Workload\WorkloadConfigController;
use App\Http\Controllers\Workload\WorkloadEntryController;
use App\Http\Controllers\Workload\WorkloadFormController;
use App\Http\Controllers\Workload\WorkloadFormFieldController;
use Illuminate\Support\Facades\Route;

// Report Structure API (for criteria version CRUD)
Route::prefix('report-version')->name('report-structure.')->group(function () {
    Route::get('/', [ReportStructureController::class, 'index'])->name('index');
    Route::get('/{id}', [ReportStructureController::class, 'show'])->name('show');
    Route::post('/', [ReportStructureController::class, 'store'])->name('store');
    Route::put('/{id}', [ReportStructureController::class, 'update'])->name('update');
    Route::delete('/{id}', [ReportStructureController::class, 'destroy'])->name('destroy');
});

// Criteria Config UI routes (for Blade views)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/criteria-config', function () {
        return view('criteria_config.index');
    })->name('criteria_config.index');
    Route::get('/criteria-configs', function () {
        return view('criteria_config.create');
    })->name('criteria_config.create');
    Route::get('/criteria-config/{id}/edit', function ($id) {
        return view('criteria_config.edit', ['id' => $id]);
    })->name('criteria_config.edit');
    Route::get('/criteria-evaluators', function () {
        return view('criteria_config.evaluators');
    })->name('criteria_config.evaluators');
});

Route::prefix('reports')->name('reports.')->group(function () {
    // Reports CRUD
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/{id}', [ReportController::class, 'show'])->name('show');
    Route::post('/', [ReportController::class, 'store'])->name('store');
    Route::put('/{id}', [ReportController::class, 'update'])->name('update');
    Route::delete('/{id}', [ReportController::class, 'destroy'])->name('destroy');

    // Quantity Scores
    Route::prefix('{reportId}/quantity-scores')->name('quantity-scores.')->group(function () {
        Route::post('/', [ReportController::class, 'addQuantityScores'])->name('store');
        Route::put('/', [ReportController::class, 'updateQuantityScores'])->name('update');
    });

    // Quality Scores
    Route::prefix('/{reportId}/quality-scores')->name('quality-scores.')->group(function () {
        Route::post('/', [ReportController::class, 'addQualityScores'])->name('store');
        Route::put('/', [ReportController::class, 'updateQualityScores'])->name('update');
    });

    // Evidence Answers
    Route::prefix('/{reportId}/evidence-answers')->name('evidence-answers.')->group(function () {
        Route::post('/', [ReportController::class, 'addEvidenceAnswers'])->name('store');
        Route::put('/', [ReportController::class, 'updateEvidenceAnswers'])->name('update');
    });
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/workload-config', [WorkloadConfigController::class, 'index'])->name('workload-config.index');
    Route::get('/workload-quantity-sub-criterias', [WorkloadConfigController::class, 'quantitySubCriteriaNav'])
        ->name('workload-config.quantity-sub-criterias');
    Route::get('/workload-sub-blocks', [WorkloadConfigController::class, 'subBlocks'])
        ->name('workload-config.sub-blocks');
    Route::post('/workload-config/save', [WorkloadConfigController::class, 'save'])
        ->name('workload-config.save');

    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::get('/{id}', [SubjectController::class, 'show'])->name('show');
        Route::post('/', [SubjectController::class, 'store'])->name('store');
        Route::post('/reorder', [SubjectController::class, 'reorder'])->name('reorder');
        Route::put('/{id}', [SubjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [SubjectController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('workload-forms')->name('workload-forms.')->group(function () {
        Route::get('/', [WorkloadFormController::class, 'index'])->name('index');
        Route::get('/{id}', [WorkloadFormController::class, 'show'])->name('show');
        Route::post('/', [WorkloadFormController::class, 'store'])->name('store');
        Route::put('/{id}', [WorkloadFormController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkloadFormController::class, 'destroy'])->name('destroy');

        Route::get('/{workloadFormId}/fields', [WorkloadFormFieldController::class, 'index'])->name('fields.index');
        Route::post('/{workloadFormId}/fields', [WorkloadFormFieldController::class, 'store'])->name('fields.store');
    });

    Route::prefix('workload-form-fields')->name('workload-form-fields.')->group(function () {
        Route::put('/{id}', [WorkloadFormFieldController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkloadFormFieldController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('workload-entries')->name('workload-entries.')->group(function () {
        Route::get('/', [WorkloadEntryController::class, 'index'])->name('index');
        Route::get('/{id}', [WorkloadEntryController::class, 'show'])->name('show');
        Route::post('/', [WorkloadEntryController::class, 'store'])->name('store');
        Route::put('/{id}', [WorkloadEntryController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkloadEntryController::class, 'destroy'])->name('destroy');
    });
});
