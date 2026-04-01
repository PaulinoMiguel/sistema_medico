<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Dra. Peralta',
            'email' => 'doctora@mediapp.local',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'specialty' => 'Urologia',
            'is_active' => true,
        ]);
    }
}
