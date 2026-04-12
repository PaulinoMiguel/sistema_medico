<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Minimal seeder for a brand-new install.
 *
 * Creates only:
 *  - The catalog of permissions and the 5 role templates (so the super admin
 *    can assign a role when creating the first doctor).
 *  - The super admin user (panel /admin), used by the developer to create
 *    doctors in the "limited plan" sales model.
 *
 * After running this seeder, the system has zero doctors, secretaries,
 * clinics, patients or any operational data — exactly the state a real
 * customer would receive on first install.
 *
 * Usage:
 *   php artisan migrate:fresh
 *   php artisan db:seed --class=FreshInstallSeeder
 */
class FreshInstallSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        Admin::firstOrCreate(
            ['email' => 'admin@mediapp.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
