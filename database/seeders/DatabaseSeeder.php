<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('OWNER_EMAIL', 'admin@example.com')],
            [
                'name' => 'Owner',
                'password' => env('OWNER_PASSWORD', 'password'),
                'role' => 'owner',
                'email_verified_at' => now(),
            ]
        );
    }
}
