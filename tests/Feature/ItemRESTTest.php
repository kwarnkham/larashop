<?php

namespace Tests\Feature;

use App\Http\Controllers\ItemController;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemRESTTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::query()->whereRelation('roles', 'name', 'admin')->first();
    }

    public function test_create_an_item(): void
    {
        $data = [
            'name' => fake()->unique()->name,
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(1000, 10000),
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/items', $data);
        $response->assertCreated();

        $this->assertEquals($response->json()['name'], $data['name']);
        $this->assertDatabaseCount('items', 1);
    }

    public function test_list_items(): void
    {
        Item::factory()->count(30)->create();
        $response = $this->getJson('/api/items');
        $response->assertOk();
        $response->assertJsonCount(ItemController::PER_PAGE, 'pagination.data');
    }

    public function test_find_an_item(): void
    {
        Item::factory()->count(30)->create();
        $item = Item::query()->inRandomOrder()->first();
        $response = $this->getJson('/api/items/'.$item->id);
        $response->assertOk();
        $this->assertEquals($item->id, $response->json()['id']);
    }

    public function test_update_an_item(): void
    {
        $item = Item::factory()->create();

        $data = [
            'name' => fake()->unique()->name,
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(1000, 10000),
            'status' => 'inactive',
        ];

        $response = $this->actingAs($this->admin)->putJson('/api/items/'.$item->id, $data);
        $response->assertOk();

        $item->refresh();

        $this->assertEquals($response->json()['name'], $data['name']);
        $this->assertEquals($response->json()['description'], $data['description']);
    }

    public function test_delete_an_item(): void
    {
        $item = Item::factory()->create();
        $response = $this->actingAs($this->admin)->deleteJson('/api/items/'.$item->id);
        $response->assertNoContent();
        $this->assertNotNull($item->fresh()->deleted_at);
        $this->assertDatabaseCount('items', 1);
    }

    public function test_upload_item_picture()
    {
        Storage::fake('s3');
        $item = Item::factory()->create();
        $picture = UploadedFile::fake()->image('foo.jpg');
        $response = $this->actingAs($this->admin)
            ->postJson('/api/items/'.$item->id.'/upload-picture', ['picture' => $picture]);

        $response->assertCreated();
        $this->assertDatabaseCount('pictures', 1);
        Storage::assertExists($response->json()['name']);
    }
}
