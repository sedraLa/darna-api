<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    // Shared dev password for all seeded users: "password"

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@darna.test'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
                'role'     => UserRole::Admin,
            ]
        );

        $officeEmails = [
            'office1@darna.test',
            'office2@darna.test',
            'office3@darna.test',
            'office4@darna.test',
            'office5@darna.test',
        ];

        foreach ($officeEmails as $i => $email) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => 'Office User ' . ($i + 1),
                    'password' => Hash::make('password'),
                    'role'     => UserRole::Office,
                ]
            );
        }

        $customerEmails = [
            'customer1@darna.test',
            'customer2@darna.test',
            'customer3@darna.test',
        ];

        foreach ($customerEmails as $i => $email) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => 'Customer ' . ($i + 1),
                    'password' => Hash::make('password'),
                    'role'     => UserRole::Customer,
                ]
            );
        }
    }
}
