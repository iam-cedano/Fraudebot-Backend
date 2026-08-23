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
                'role' => 'admin',
            ]
        );
        $user->forceFill(['role' => 'admin', 'is_active' => true])->save();

        $user->tokens()->delete();

        $token = $user->createToken(
            'local-admin-token',
            ['admin:write'],
            now()->addHour(),
        )->plainTextToken;

        return response()->json(['user' => $user->username, 'email' => $user->email, 'token' => $token]);
    }
}
