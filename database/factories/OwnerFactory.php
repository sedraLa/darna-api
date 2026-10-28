<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Owner>
 */
class OwnerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'office_id' => Office::factory(),
            'name'      => fake()->name(),
            'phone'     => fake()->phoneNumber(),
            'email'     => fake()->optional(0.6)->safeEmail(),
        ];
    }
}
