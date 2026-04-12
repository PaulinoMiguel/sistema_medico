<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Catalogo completo de permisos atomicos del sistema, agrupado por modulo.
     * Cada permiso aqui se materializa en la tabla `permissions`.
     */
    public const PERMISSIONS = [
        // Pacientes
        'patients.view',
        'patients.create',
        'patients.update',
        'patients.delete',
        'patients.view-history',
        'patients.transfer',

        // Citas / Turnos
        'appointments.view',
        'appointments.create',
        'appointments.update',
        'appointments.cancel',
        'appointments.change-status',

        // Clinico
        'consultations.view',
        'consultations.create',
        'consultations.update',
        'consultations.sign',
        'prescriptions.view',
        'prescriptions.create',
        'prescriptions.print',
        'prescriptions.delete',

        // Ingresos
        'payments.view',
        'payments.create',
        'payments.delete',
        'services.view',
        'services.manage',

        // Caja
        'cash-register.view',
        'cash-register.open',
        'cash-register.close',

        // Egresos
        'expenses.view',
        'expenses.create',
        'expenses.update',
        'expenses.delete',
        'expenses.view-summary',
        'expense-categories.manage',

        // Administracion
        'clinics.view',
        'clinics.manage',
        'staff.view',
        'staff.manage',
        'roles.manage',
        'settings.manage',
    ];

    public function run(): void
    {
        // Reset spatie's cached permissions before re-seeding
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create every atomic permission
        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // === doctor_admin ===
        // El "dueno" del consultorio. Tiene todo, incluido configurar roles
        // y settings. En la practica suele ser el doctor que pago el sistema.
        $doctorAdmin = Role::firstOrCreate(['name' => 'doctor_admin', 'guard_name' => 'web']);
        $doctorAdmin->syncPermissions(self::PERMISSIONS);

        // === doctor_associate ===
        // Doctor secundario (ej. los 3 doctores que comparten una clinica
        // donde uno solo es el admin). Hace todo lo clinico y financiero
        // pero no toca administracion ni configuracion del sistema.
        $doctorAssociate = Role::firstOrCreate(['name' => 'doctor_associate', 'guard_name' => 'web']);
        $doctorAssociate->syncPermissions(array_diff(self::PERMISSIONS, [
            'services.manage',
            'clinics.manage',
            'staff.view',
            'staff.manage',
            'roles.manage',
            'settings.manage',
            'patients.transfer',
        ]));

        // === secretary_limited ===
        // Caso urologa: secretaria SIN acceso a gastos ni resumen financiero.
        // Apoya en pacientes, citas y registro de cobros.
        // NOTA: NO tiene payments.view ni services.view a proposito — su unica
        // ventana a ingresos es Corte de Caja (sesion activa). El doctor puede
        // tener cobros fuera del consultorio (cirugias) que la secretaria no
        // debe ver. payments.create se mantiene para que pueda registrar
        // cobros desde el flujo de caja.
        $secretaryLimited = Role::firstOrCreate(['name' => 'secretary_limited', 'guard_name' => 'web']);
        $secretaryLimited->syncPermissions([
            'patients.view',
            'patients.create',
            'patients.update',
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.cancel',
            'appointments.change-status',
            'consultations.view',
            'prescriptions.view',
            'payments.create',
            'cash-register.view',
            'cash-register.open',
            'cash-register.close',
            'clinics.view',
        ]);

        // === secretary_full ===
        // Caso 3 doctores: igual que secretary_limited PLUS gastos, resumen
        // y poder imprimir recetas (para entregar al paciente).
        $secretaryFull = Role::firstOrCreate(['name' => 'secretary_full', 'guard_name' => 'web']);
        $secretaryFull->syncPermissions(array_merge(
            $secretaryLimited->permissions->pluck('name')->toArray(),
            [
                'expenses.view',
                'expenses.create',
                'expenses.update',
                'expenses.view-summary',
                'expense-categories.manage',
                'prescriptions.print',
            ]
        ));

        // === nurse ===
        // Asistencia clinica (futuro). Solo lectura de pacientes/consultas
        // y cambio de estado de citas.
        $nurse = Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web']);
        $nurse->syncPermissions([
            'patients.view',
            'patients.view-history',
            'appointments.view',
            'appointments.change-status',
            'consultations.view',
            'clinics.view',
        ]);
    }
}
