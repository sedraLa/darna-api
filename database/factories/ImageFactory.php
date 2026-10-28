<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    public function definition(): array
    {
        $width  = fake()->randomElement([800, 1024, 1280]);
        $height = fake()->randomElement([600, 768, 960]);

        return [
            'path'           => "images/properties/{$width}x{$height}_" . fake()->uuid() . '.jpg',
            'imageable_id'   => null,
            'imageable_type' => null,
        ];
    }
}
