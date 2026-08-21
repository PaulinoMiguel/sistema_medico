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
 * Solo para secretarias, no para doctores: la caja la maneja la secretaria,
 * y es el doctor quien despues recibe conforme. Si el doctor pudiera
 * registrar gastos, terminaria aprobando movimientos suyos y se perderia la
 * separacion que le da sentido a esa aprobacion.
 *
 * En un consultorio sin secretaria, donde el propio doctor lleva la caja, el
 * permiso se le puede conceder desde Administracion > Roles y permisos.
 *
 * Aditivo e idempotente.
 */
return new class extends Migration
{
    private const ROLES = ['secretary_limited', 'secretary_full'];

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
