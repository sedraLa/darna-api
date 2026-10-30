<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tokens:generate', function () {
    $emails = [
        'admin@darna.test',
        'office1@darna.test',
        'office2@darna.test',
        'customer1@darna.test',
    ];

    foreach ($emails as $email) {
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->tokens()->where('name', 'dev-token')->delete();
            $token = $user->createToken('dev-token')->plainTextToken;
            $this->line("{$user->role->value} ({$email}): {$token}");
        }
    }
})->purpose('Generate plain Sanctum tokens for seeded development accounts');
