<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ScammerController;

Route::middleware('auth.sanctum')->group(function () {
    Route::apiResource('organizations', OrganizationController::class);
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