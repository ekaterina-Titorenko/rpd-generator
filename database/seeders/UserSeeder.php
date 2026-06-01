<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Преподаватель',
                'role' => 'teacher',
                'password' => Hash::make('password'),
            ]
        );
    }
}