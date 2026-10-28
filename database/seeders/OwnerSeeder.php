<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Owner;
use Illuminate\Database\Seeder;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $offices = Office::all();

        if ($offices->isEmpty()) {
            return;
        }

        $existing = Owner::count();

        if ($existing >= 50) {
            return;
        }

        $needed = 50 - $existing;

        $offices->each(function (Office $office) use (&$needed) {
            $perOffice = (int) ceil($needed / 5);

            for ($i = 0; $i < $perOffice && $needed > 0; $i++) {
                Owner::create([
                    'office_id' => $office->id,
                    'name'      => fake()->name(),
                    'phone'     => '+963 9' . rand(10000000, 99999999),
                    'email'     => fake()->optional(0.6)->safeEmail(),
                ]);
                $needed--;
            }
        });
    }
}
