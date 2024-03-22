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

    public function test_creating_a_default_address_will_make_existing_addresses_default_to_false()
    {
        $user = User::factory()->has(Address::factory())->create();
        $this->assertDatabaseCount('addresses', 1);
        $existingAddress = Address::query()->first();
        $this->assertTrue((bool) $existingAddress->default);

        $address = Address::factory()->make();
        $response = $this->actingAs($user)->postJson('api/addresses', $address->toArray());
        $this->assertFalse((bool) $existingAddress->fresh()->default);
        $this->assertTrue((bool) $response->json()['default']);

        $address = Address::factory()->state(['default' => false])->make();
        $defaultAddress = Address::query()->find($response->json()['id']);
        $response = $this->actingAs($user)->postJson('api/addresses', $address->toArray());
        $this->assertFalse((bool) $response->json()['default']);
        $this->assertTrue((bool) $defaultAddress->default);
    }
}
