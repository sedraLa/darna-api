<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Governorate;
use App\Models\Image;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PropertySeeder extends Seeder
{
    private const TARGET = 3000;
    private const CHUNK  = 500;

    public function run(): void
    {
        $existing = Property::count();

        if ($existing >= self::TARGET) {
            $this->command->info("Properties already seeded ({$existing}). Skipping.");
            return;
        }

        $needed = self::TARGET - $existing;

        $governorateIds = Governorate::pluck('id')->toArray();
        $officeIds      = Office::pluck('id')->toArray();
        $ownerIds       = Owner::pluck('id')->toArray();

        if (empty($officeIds) || empty($ownerIds) || empty($governorateIds)) {
            $this->command->error('Run GovernorateSeeder, OfficeSeeder, and OwnerSeeder first.');
            return;
        }

        $purposes   = array_column(PropertyPurpose::cases(), 'value');
        $statuses   = array_column(PropertyStatus::cases(), 'value');
        $types      = array_column(PropertyType::cases(), 'value');
        $currencies = array_column(Currency::cases(), 'value');

        $titlePrefixes = [
            'apartment' => ['Modern Apartment', 'Spacious Apartment', 'Cozy Flat', 'Luxury Apartment', 'Studio Flat', 'Family Apartment'],
            'shop'      => ['Commercial Shop', 'Retail Space', 'Corner Shop', 'Prime Shop', 'Ground Floor Shop', 'Showroom'],
            'land'      => ['Residential Land', 'Commercial Plot', 'Agricultural Land', 'Building Land', 'Investment Plot', 'Corner Land'],
        ];

        $neighborhoods = [
            'Mezzeh', 'Malki', 'Kafr Sousa', 'Bab Touma', 'Arnous',
            'Aziziyeh', 'Sulaymaniyah', 'Shahba', 'Masharqa', 'Hamra',
            'Corniche', 'Ramel', 'Slibeh', 'Inshaat', 'Nuzha',
            'Khaldiyeh', 'Bab Sbaa', 'Waer', 'Ghouta', 'Marjeh',
        ];

        $now      = Carbon::now()->toDateTimeString();
        $seeded   = 0;
        $imageRows = [];

        while ($seeded < $needed) {
            $chunk = [];
            $size  = min(self::CHUNK, $needed - $seeded);

            for ($i = 0; $i < $size; $i++) {
                $type     = $types[array_rand($types)];
                $currency = $currencies[array_rand($currencies)];

                $price = match ($currency) {
                    'SYP' => rand(5_000_000, 500_000_000),
                    'USD' => rand(10_000, 500_000),
                    'EUR' => rand(10_000, 450_000),
                };

                $area = match ($type) {
                    'apartment' => round(rand(50, 400) + rand(0, 99) / 100, 2),
                    'shop'      => round(rand(20, 300) + rand(0, 99) / 100, 2),
                    'land'      => round(rand(100, 5000) + rand(0, 99) / 100, 2),
                };

                $prefixes    = $titlePrefixes[$type];
                $prefix      = $prefixes[array_rand($prefixes)];
                $neighborhood = $neighborhoods[array_rand($neighborhoods)];

                $chunk[] = [
                    'title'          => $prefix . ' in ' . $neighborhood,
                    'description'    => rand(0, 4) > 0 ? 'A well-located ' . $type . ' available in ' . $neighborhood . '. Great investment opportunity.' : null,
                    'price'          => $price,
                    'currency'       => $currency,
                    'purpose'        => $purposes[array_rand($purposes)],
                    'status'         => $statuses[array_rand($statuses)],
                    'type'           => $type,
                    'area'           => $area,
                    'governorate_id' => $governorateIds[array_rand($governorateIds)],
                    'office_id'      => $officeIds[array_rand($officeIds)],
                    'owner_id'       => $ownerIds[array_rand($ownerIds)],
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }

            DB::table('properties')->insert($chunk);
            $seeded += $size;

            $this->command->info("Inserted {$seeded}/{$needed} properties...");
        }

        $this->seedImages();
    }

    private function seedImages(): void
    {
        $existingImageCount = Image::where('imageable_type', Property::class)->count();

        if ($existingImageCount > 0) {
            $this->command->info("Property images already seeded. Skipping.");
            return;
        }

        $propertyIds = Property::pluck('id')->toArray();
        $imageRows   = [];
        $now         = Carbon::now()->toDateTimeString();

        foreach ($propertyIds as $propertyId) {
            $count = rand(0, 5);
            for ($j = 0; $j < $count; $j++) {
                $imageRows[] = [
                    'imageable_id'   => $propertyId,
                    'imageable_type' => Property::class,
                    'path'           => 'images/properties/property_' . $propertyId . '_' . ($j + 1) . '.jpg',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }

            if (count($imageRows) >= 1000) {
                DB::table('images')->insert($imageRows);
                $imageRows = [];
            }
        }

        if (!empty($imageRows)) {
            DB::table('images')->insert($imageRows);
        }

        $this->command->info('Property images seeded.');
    }
}
