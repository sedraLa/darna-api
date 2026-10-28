<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    private static array $titleTemplates = [
        'apartment' => ['Modern Apartment in', 'Spacious Apartment in', 'Cozy Flat in', 'Luxury Apartment in', 'Studio Apartment in'],
        'shop'      => ['Commercial Shop in', 'Retail Space in', 'Corner Shop in', 'Prime Shop in', 'Ground Floor Shop in'],
        'land'      => ['Residential Land in', 'Commercial Plot in', 'Agricultural Land in', 'Building Land in', 'Investment Land in'],
    ];

    public function definition(): array
    {
        $type     = fake()->randomElement(PropertyType::cases());
        $purpose  = fake()->randomElement(PropertyPurpose::cases());
        $currency = fake()->randomElement(Currency::cases());
        $status   = fake()->randomElement(PropertyStatus::cases());

        $price = match ($currency) {
            Currency::SYP => fake()->numberBetween(5_000_000, 500_000_000),
            Currency::USD => fake()->numberBetween(10_000, 500_000),
            Currency::EUR => fake()->numberBetween(10_000, 450_000),
        };

        $area = match ($type) {
            PropertyType::Apartment => fake()->randomFloat(2, 50, 400),
            PropertyType::Shop      => fake()->randomFloat(2, 20, 300),
            PropertyType::Land      => fake()->randomFloat(2, 100, 5000),
        };

        $templates = self::$titleTemplates[$type->value];
        $title     = fake()->randomElement($templates) . ' ' . fake()->city();

        return [
            'title'          => $title,
            'description'    => fake()->optional(0.8)->paragraph(3),
            'price'          => $price,
            'currency'       => $currency,
            'purpose'        => $purpose,
            'status'         => $status,
            'type'           => $type,
            'area'           => $area,
            'governorate_id' => Governorate::factory(),
            'office_id'      => Office::factory(),
            'owner_id'       => Owner::factory(),
        ];
    }
}
