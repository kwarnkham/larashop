<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressRESTTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_create_an_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->make();
        $response = $this->actingAs($user)->postJson('api/addresses', $address->toArray());

        $response->assertCreated();
        $this->assertDatabaseCount('addresses', 1);
    }
}
