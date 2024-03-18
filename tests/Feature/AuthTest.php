<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_regsiter_a_user(): void
    {
        $existingUsersCount = User::query()->count();
        $response = $this->postJson('/api/auth/register', [
            'email' => fake()->safeEmail(),
            'name' => fake()->firstName(),
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $this->assertDatabaseCount('users', $existingUsersCount + 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $response->assertCreated();
        $user = User::query()->where('email', $response->json()['user']['email'])->first();
        // $this->assertAuthenticated();
    }

    public function test_login_a_user(): void
    {
        $email = fake()->safeEmail();
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        $user = User::query()->where('email', $email)->first();

        $this->assertAuthenticated('auth:sanctum');
    }
}
