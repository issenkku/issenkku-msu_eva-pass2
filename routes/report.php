<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportStructureController;
use App\Http\Controllers\ReportController;

Route::prefix('report-version')->name('report-structure.')->group(function () {
    // Report Structure Routes
    Route::get('/', [ReportStructureController::class, 'index'])->name('index');
    Route::get('/{id}', [ReportStructureController::class, 'show'])->name('show');
    Route::post('/', [ReportStructureController::class, 'store'])->name('store');
    Route::put('/{id}', [ReportStructureController::class, 'update'])->name('update');
    Route::delete('/{id}', [ReportStructureController::class, 'destroy'])->name('destroy');
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
