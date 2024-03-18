<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User $user */
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_request_email_verification()
    {
        $response = $this->actingAs($this->user)->postJson('api/auth/email-verification');
        $response->assertNoContent();
    }

    public function test_verify_email()
    {
        $this->withExceptionHandling();
        $code = $this->user->getEmailVerificationCode();
        $response = $this->actingAs($this->user)->postJson('api/auth/verify-email', [
            'code' => $code
        ]);
        $response->assertNoContent();
        $this->assertTrue($this->user->fresh()->hasVerifiedEmail());
    }

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

    public function test_change_password(): void
    {
        $password = 'password';
        $newPassword = 'new_password';
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create(['password' => bcrypt($password)]);

        $token = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ])->json()['token'];

        $response = $this->actingAs($user)->postJson('/api/auth/change-password', [
            'password' => 'wrong_password',
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword
        ]);

        $response->assertForbidden();

        $response = $this->postJson('/api/auth/change-password', [
            'password' => $password,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
            'logout_all_other_devices' => true
        ], headers: [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}
