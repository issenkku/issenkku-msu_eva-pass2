<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportStructureController;

Route::prefix('report-structure')->group(function () {
    // Report Structure Routes
    Route::get('/', [ReportStructureController::class, 'index']);
    Route::get('/{id}', [ReportStructureController::class, 'show']);
    Route::post('/', [ReportStructureController::class, 'store']);
    Route::put('/{id}', [ReportStructureController::class, 'update']);
    Route::delete('/{id}', [ReportStructureController::class, 'destroy']);
});
