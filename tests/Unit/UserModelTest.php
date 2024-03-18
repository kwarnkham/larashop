<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_method_sendEmailVerificationNotification(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->sendEmailVerificationNotification();

        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );
        Notification::assertCount(1);
    }

    public function test_method_sendPasswordResetCode()
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->sendPasswordResetCode();

        Notification::assertSentTo(
            [$user],
            ResetPassword::class
        );
        Notification::assertCount(1);
    }
}
