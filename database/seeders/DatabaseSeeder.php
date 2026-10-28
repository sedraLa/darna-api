<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GovernorateSeeder::class,
            UserSeeder::class,
            OfficeSeeder::class,
            OwnerSeeder::class,
            PropertySeeder::class,
        ]);

        $this->printDevTokens();
    }

    private function printDevTokens(): void
    {
        $accounts = [
            'admin@darna.test',
            'office1@darna.test',
            'customer1@darna.test',
        ];

        $this->command->newLine();
        $this->command->info('===== Dev Tokens (printed once, not stored in plaintext) =====');

        foreach ($accounts as $email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                continue;
            }

            $user->tokens()->where('name', 'dev-token')->delete();
            $token = $user->createToken('dev-token')->plainTextToken;

            $this->command->line("  [{$user->role->value}] {$email}  =>  {$token}");
        }

        $this->command->info('==============================================================');
        $this->command->newLine();
    }
}
