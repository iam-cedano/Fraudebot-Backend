<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\OrganizationController;
use App\Http\Controllers\Public\ScammerController;
use App\Http\Controllers\Public\ReportController;

Route::prefix('public')->group(function () {
    Route::get('organizations/{id}', [OrganizationController::class, 'show']);
    Route::get('organizations/{id}/calendar/{year}', [OrganizationController::class, 'calendar']);
    Route::get('organizations/{id}/contacts', [OrganizationController::class, 'contacts']);

    Route::get('scammers/{id}', [ScammerController::class, 'show']);
    Route::get('scammers/{id}/calendar/{year}', [ScammerController::class, 'calendar']);
    Route::get('scammers/{id}/contacts', [ScammerController::class, 'contacts']);

    Route::get('reports', [ReportController::class, 'index']);

    Route::get('healthcheck', function () {
        return response()->json(['status' => 'ok']);
    });
});
