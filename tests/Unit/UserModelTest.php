<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_method_sendEmailVerificationNotification(): void
    {
        $user = User::factory()->create();
        $user->refreshEmailVerificationCode();
        $user->sendEmailVerificationNotification();
        $this->assertTrue(Cache::has($user->id . ".email_verification_code"));
    }
}
