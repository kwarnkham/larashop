<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
