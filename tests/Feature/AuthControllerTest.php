<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_reporter_can_register_and_receive_scoped_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'reporter',
            'email' => 'reporter@example.com',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'reporter')
            ->assertJsonStructure(['token', 'expires_at', 'user']);

        $this->assertDatabaseHas('users', [
            'email' => 'reporter@example.com',
            'role' => 'reporter',
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'secure-pass-123',
            'is_active' => false,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'secure-pass-123',
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed');
    }
}
