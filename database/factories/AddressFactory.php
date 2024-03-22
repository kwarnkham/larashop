<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $country = fake()->country();

        return [
            'name' => fake()->unique()->name(),
            'country' => $country,
            'province' => "$country's province",
            'township' => "$country's township",
            'city' => fake()->city(),
            'street_one' => fake()->streetAddress(),
            'phone' => fake()->phoneNumber(),
            'zip_code' => fake()->postcode(),
        ];
    }
}
