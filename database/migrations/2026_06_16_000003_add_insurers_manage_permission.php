<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permiso para gestionar el catálogo de aseguradoras y códigos. Se asigna
     * a las secretarias (son ellas quienes lo mantienen) y a ambos doctores.
     * Aditivo e idempotente para no pisar instalaciones ya sembradas.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::firstOrCreate(['name' => 'insurers.manage', 'guard_name' => 'web']);

        foreach (['doctor_admin', 'doctor_associate', 'secretary_limited', 'secretary_full'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            $role?->givePermissionTo($perm);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::where('name', 'insurers.manage')->where('guard_name', 'web')->first();
        $perm?->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
