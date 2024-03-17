<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $response->assertCreated();
    }
}
