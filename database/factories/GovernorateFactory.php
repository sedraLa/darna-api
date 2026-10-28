<?php

namespace Database\Factories;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Governorate>
 */
class GovernorateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
        ];
    }
}
