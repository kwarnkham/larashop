<?php

namespace Tests\Feature;

use App\Http\Controllers\ItemController;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemRESTTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::first();
    }

    public function test_create_an_item(): void
    {
        $data = [
            'name' => fake()->unique()->name,
            'description' => fake()->sentence()
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
}
