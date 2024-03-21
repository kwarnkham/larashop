<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserRESTTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::factory()->create();
    }

    public function test_upload_user_picture(): void
    {
        Storage::fake('s3');
        $picture = UploadedFile::fake()->create('user.jpg');
        $response = $this
            ->actingAs($this->user)
            ->postJson('api/users/upload-picture', ['picture' => $picture]);

        $response->assertOk();
        Storage::assertExists($response->json()['picture']);
    }
}
