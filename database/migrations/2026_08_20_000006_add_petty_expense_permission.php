<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permiso para registrar gastos menores contra la caja abierta.
 *
 * Es deliberadamente estrecho: NO da acceso al modulo de gastos ni a las
 * finanzas de la clinica. La secretaria limitada puede sacar dinero de la
 * gaveta y dejarlo asentado, pero sigue sin ver cuanto gasta el consultorio,
 * que es el aislamiento que el proyecto ya tenia por diseno.
 *
 * Aditivo e idempotente.
 */
return new class extends Migration
{
    private const ROLES = ['doctor_admin', 'doctor_associate', 'secretary_limited', 'secretary_full'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::firstOrCreate(['name' => 'expenses.petty-create', 'guard_name' => 'web']);

        foreach (self::ROLES as $roleName) {
            Role::where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($perm);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::where('name', 'expenses.petty-create')->where('guard_name', 'web')->first()?->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
