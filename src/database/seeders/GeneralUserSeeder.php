<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GeneralUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'user1@example.com'],
            [
                'name' => '一般ユーザー1',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => '一般ユーザー2',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user3@example.com'],
            [
                'name' => '一般ユーザー3',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );
    }
}
