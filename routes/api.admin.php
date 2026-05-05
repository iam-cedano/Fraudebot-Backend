<?php

use App\Http\Controllers\Admin\DevelopmentController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\ScammerController;

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('organizations/{organization}/scammers', [OrganizationController::class, 'getScammers']);
    Route::post('organizations/{organization}/scammer/{scammer}', [OrganizationController::class, 'addScammer']);
    Route::post('organizations/{organization}/payment', [OrganizationController::class, 'createPaymentMethod']);

    Route::post('scammers/{scammer}/restore', [ScammerController::class, 'restore']);
    Route::post('organizations/{organization}/restore', [OrganizationController::class, 'restore']);

    Route::put('scammers/profile/{profile}', [ScammerController::class, 'updateProfile']);
    Route::post('scammers/{scammer}/profiles', [ScammerController::class, 'createProfile']);
    Route::post('scammers/{scammer}/payment', [ScammerController::class, 'createPaymentMethod']);

    Route::apiResource('scammers', ScammerController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('organizations', OrganizationController::class)->only(['store', 'update', 'destroy']);



    Route::post('/token', [DevelopmentController::class, 'token'])->withoutMiddleware('auth:sanctum');
});

