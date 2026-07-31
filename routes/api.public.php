<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\OrganizationController;
use App\Http\Controllers\Public\ScammerController;
use App\Http\Controllers\Public\ReportController;

Route::prefix('public')->group(function () {
    Route::get('organizations', [OrganizationController::class, 'index']);
    Route::get('organizations/{organization}', [OrganizationController::class, 'show']);

    Route::get('scammers', [ScammerController::class, 'index']);

    Route::get('reports', [ReportController::class, 'index']);

    Route::get('healthcheck', function () {
        return response()->json(['status' => 'ok']);
    });
});
