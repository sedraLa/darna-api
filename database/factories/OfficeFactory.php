<?php

namespace Database\Factories;

use App\Models\Governorate;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory()->office(),
            'governorate_id' => Governorate::factory(),
            'name'           => fake()->company() . ' Real Estate',
            'phone'          => fake()->phoneNumber(),
            'address'        => fake()->streetAddress(),
        ];
    }
}
