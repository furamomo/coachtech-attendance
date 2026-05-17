<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者ユーザー',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );
    }
}
