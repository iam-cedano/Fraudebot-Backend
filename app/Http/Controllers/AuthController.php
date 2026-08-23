<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            ...$request->safe()->only(['username', 'email', 'password']),
            'role' => 'reporter',
            'is_active' => true,
        ]);

        return response()->json($this->tokenResponse($user, 'registration'), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! $user->is_active || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are invalid.']]);
        }

        return response()->json($this->tokenResponse(
            $user,
            $request->validated('device_name', 'api-client'),
        ));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(null, 204);
    }

    private function tokenResponse(User $user, string $deviceName): array
    {
        $abilities = in_array($user->role, ['admin', 'moderator'], true)
            ? ['admin:write']
            : [];

        $token = $user->createToken(
            $deviceName,
            $abilities,
            now()->addMinutes((int) config('sanctum.expiration')),
        );

        return [
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toISOString(),
            'user' => $user->only(['id', 'username', 'email', 'role']),
        ];
    }
}
