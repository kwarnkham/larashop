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

        $token = $response->json()['token'];
        $this->getJson('api/user', headers: [
            'Authorization' => "Bearer $token",
        ])->assertOk();

        $this->assertAuthenticatedAs($user, 'sanctum');
    }

    public function test_login_a_user(): void
    {
        $password = 'password';
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk();

        $token = $response->json()['token'];
        $this->getJson('api/user', headers: [
            'Authorization' => "Bearer $token",
        ])->assertOk();

        $this->assertAuthenticatedAs($user, 'sanctum');
    }
}
