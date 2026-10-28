<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Governorate;
use App\Models\Image;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $officeUsers    = User::where('role', UserRole::Office->value)->get();
        $governorates   = Governorate::all();
        $officeData     = [
            ['name' => 'Al-Sham Real Estate', 'address' => 'Mezzeh, Damascus'],
            ['name' => 'Aleppo Properties',   'address' => 'Aziziyeh, Aleppo'],
            ['name' => 'Coast Homes',          'address' => 'Corniche, Latakia'],
            ['name' => 'Homs Estates',         'address' => 'Khaldiyeh, Homs'],
            ['name' => 'Syria Realty Group',   'address' => 'Malki, Damascus'],
        ];

        foreach ($officeUsers as $index => $user) {
            $govIndex = $index % $governorates->count();
            $data     = $officeData[$index] ?? $officeData[0];

            $office = Office::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'governorate_id' => $governorates[$govIndex]->id,
                    'name'           => $data['name'],
                    'phone'          => '+963 11 ' . rand(1000000, 9999999),
                    'address'        => $data['address'],
                ]
            );

            if ($office->logo()->doesntExist()) {
                $office->logo()->create([
                    'path' => 'images/logos/office_' . $office->id . '.png',
                ]);
            }
        }
    }
}
