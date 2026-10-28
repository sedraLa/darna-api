<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Damascus', 'Aleppo', 'Homs', 'Hama', 'Latakia',
            'Tartus', 'Deir ez-Zor', 'Al-Hasakah', 'Raqqa', 'Daraa',
            'As-Suwayda', 'Quneitra', 'Idlib', 'Rif Dimashq',
        ];

        foreach ($names as $name) {
            Governorate::firstOrCreate(['name' => $name]);
        }
    }
}
