<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserRESTTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::factory()->create();
        $this->admin = User::query()->whereRelation('roles', 'name', 'admin')->first();
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

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(30)->create();
        $response = $this
            ->actingAs($this->admin)
            ->getJson('api/users');

        $response->assertOk();
        $response->assertJsonCount(UserController::PER_PAGE, 'pagination.data');

        $response = $this
            ->actingAs($this->user)
            ->getJson('api/users');

        $response->assertForbidden();
    }

    public function test_admin_find_a_user(): void
    {
        $response = $this
            ->actingAs($this->admin)
            ->getJson("api/users/{$this->user->id}");

        $response->assertOk();
        $this->assertEquals($this->user->id, $response->json()['id']);
    }
}
