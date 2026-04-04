<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        Admin::firstOrCreate(
            ['email' => 'admin@mediapp.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

    }
}
