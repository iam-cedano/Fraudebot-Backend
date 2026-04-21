<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class DevelopmentController
{

    public function token(): JsonResponse
    {
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
    }
}