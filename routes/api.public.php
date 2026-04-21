<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\OrganizationController;
use App\Http\Controllers\Public\ScammerController;

Route::prefix('public')->group(function () {
    Route::apiResource('organizations', OrganizationController::class);
    Route::apiResource('scammers', ScammerController::class);

    Route::get('healthcheck', function() {
        return response()->json(['status' => 'ok']);
    });
});