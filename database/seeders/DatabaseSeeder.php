<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@hasnautama.com',
            ],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'role' => 'HRD',
                'timezone' => 'Asia/Jakarta',
            ]
        );
    }
}