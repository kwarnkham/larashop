<?php

namespace Tests\Feature;

use App\Jobs\SendEmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User */
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_request_email_verification()
    {
        Queue::fake([SendEmailVerificationCode::class]);
        $response = $this->actingAs($this->user)->postJson('api/auth/email-verification');
        $response->assertNoContent();
        Queue::assertPushed(SendEmailVerificationCode::class);
    }

    public function test_verify_email()
    {
        $this->withExceptionHandling();
        $code = $this->user->getEmailVerificationCode();
        $response = $this->actingAs($this->user)->postJson('api/auth/verify-email', [
            'code' => $code,
        ]);
        $response->assertNoContent();
        $this->assertTrue($this->user->fresh()->hasVerifiedEmail());
        $this->assertFalse(Cache::has($this->user->id.'email_verification_code'));
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

    public function test_restricted_user_cannot_login(): void
    {
        $password = 'password';
        $user = User::factory()->create(['password' => bcrypt($password), 'restricted' => true]);
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertForbidden();
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
            'new_password_confirmation' => $newPassword,
        ]);

        $response->assertForbidden();

        $response = $this->postJson('/api/auth/change-password', [
            'password' => $password,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
            'logout_all_other_devices' => true,
        ], headers: [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_user_request_to_reset_password()
    {
        Notification::fake();
        $response = $this->postJson('api/auth/forget-password', [
            'email' => $this->user->email,
        ]);

        $response->assertNoContent();
        Notification::assertNotSentTo(
            [$this->user],
            Notification::class
        );
    }

    public function test_user_reset_password()
    {
        $code = $this->user->getPasswordResetCode();

        $password = 'password_reset';

        $response = $this->postJson('api/auth/reset-password', [
            'code' => $code,
            'email' => $this->user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertNoContent();
        $this->assertTrue(Hash::check($password, $this->user->fresh()->password));
        $this->assertFalse(Cache::has($this->user.'.password_reset_code'));
    }
}
