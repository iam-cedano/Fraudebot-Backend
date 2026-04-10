<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ScammerController;

Route::get('organizations/{organization}', [OrganizationController::class, 'show']);
Route::post('organizations/{organization_id}/scammer/{scammer_id}', [OrganizationController::class, 'addScammer']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('organizations/{organization}/scammers', [OrganizationController::class, 'getScammers']);
    Route::apiResource('organizations', OrganizationController::class)->except(['show']);
    Route::apiResource('scammers', ScammerController::class);
});

Route::post('/token', function (Request $request) {
    if (app()->isProduction()) {
        return response()->json(['message' => 'Token generation is disabled in production.'], 403);
    }

    $user = User::firstOrCreate(
        [
            'username' => 'Anonymous',
            'email' => 'anon@example.com',
        ],
        [
            'password' => bcrypt('test123'),
        ]
    );

    $user->tokens()->delete();
    
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json(['user' => $user->username, 'email' => $user->email, 'token' => $token]);
});
