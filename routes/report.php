<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportStructureController;
use App\Http\Controllers\ReportController;

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
    Route::get('/criteria-config/{id}/edit', function($id) {
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
