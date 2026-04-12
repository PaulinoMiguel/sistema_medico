<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /** Roles that cannot be edited or deleted (doctor roles managed by super admin). */
    private const SYSTEM_ROLES = ['doctor_admin', 'doctor_associate'];

    /** Roles that cannot be deleted (all seeded roles). */
    private const PROTECTED_ROLES = ['doctor_admin', 'doctor_associate', 'secretary_full', 'secretary_limited', 'nurse'];

    /** Module prefix → Spanish label for grouping. */
    private const PERMISSION_MODULES = [
        'patients' => 'Pacientes',
        'appointments' => 'Turnos',
        'consultations' => 'Consultas',
        'prescriptions' => 'Recetas',
        'payments' => 'Cobros',
        'services' => 'Servicios',
        'cash-register' => 'Caja',
        'expenses' => 'Gastos',
        'expense-categories' => 'Categorias de gasto',
        'clinics' => 'Clinicas',
        'staff' => 'Personal',
        'roles' => 'Roles',
        'settings' => 'Configuracion',
    ];

    /** Permission name → Spanish label. */
    private const PERMISSION_LABELS = [
        'patients.view' => 'Ver pacientes',
        'patients.create' => 'Crear pacientes',
        'patients.update' => 'Editar pacientes',
        'patients.delete' => 'Eliminar pacientes',
        'patients.view-history' => 'Ver historial clinico',
        'patients.transfer' => 'Transferir pacientes',

        'appointments.view' => 'Ver turnos',
        'appointments.create' => 'Crear turnos',
        'appointments.update' => 'Editar turnos',
        'appointments.cancel' => 'Cancelar turnos',
        'appointments.change-status' => 'Cambiar estado de turnos',

        'consultations.view' => 'Ver consultas',
        'consultations.create' => 'Crear consultas',
        'consultations.update' => 'Editar consultas',
        'consultations.sign' => 'Firmar consultas',
        'prescriptions.view' => 'Ver recetas',
        'prescriptions.create' => 'Crear recetas',
        'prescriptions.print' => 'Imprimir recetas',
        'prescriptions.delete' => 'Eliminar recetas',

        'payments.view' => 'Ver cobros',
        'payments.create' => 'Registrar cobros',
        'payments.delete' => 'Eliminar cobros',
        'services.view' => 'Ver servicios',
        'services.manage' => 'Gestionar servicios',

        'cash-register.view' => 'Ver caja',
        'cash-register.open' => 'Abrir caja',
        'cash-register.close' => 'Cerrar caja',

        'expenses.view' => 'Ver gastos',
        'expenses.create' => 'Crear gastos',
        'expenses.update' => 'Editar gastos',
        'expenses.delete' => 'Eliminar gastos',
        'expenses.view-summary' => 'Ver resumen financiero',
        'expense-categories.manage' => 'Gestionar categorias de gasto',

        'clinics.view' => 'Ver clinicas',
        'clinics.manage' => 'Gestionar clinicas',
        'staff.view' => 'Ver personal',
        'staff.manage' => 'Gestionar personal',
        'roles.manage' => 'Gestionar roles',
        'settings.manage' => 'Gestionar configuracion',
    ];

    /** Spanish labels for role names. */
    private const ROLE_LABELS = [
        'doctor_admin' => 'Doctor Administrador',
        'doctor_associate' => 'Doctor Asociado',
        'secretary_full' => 'Secretaria Completa',
        'secretary_limited' => 'Secretaria Limitada',
        'nurse' => 'Enfermera',
    ];

    public function index()
    {
        $roles = Role::where('guard_name', 'web')
            ->withCount('permissions', 'users')
            ->get()
            ->map(function ($role) {
                $role->label = self::ROLE_LABELS[$role->name] ?? Str::title(str_replace('_', ' ', $role->name));
                $role->is_system = in_array($role->name, self::SYSTEM_ROLES);
                $role->is_protected = in_array($role->name, self::PROTECTED_ROLES);
                return $role;
            });

        // Staff users for role assignment section
        $staffUsers = User::whereHas('roles', function ($q) {
                $q->whereNotIn('name', self::SYSTEM_ROLES);
            })
            ->with('roles', 'clinics')
            ->where('status', '!=', 'inactive')
            ->orderBy('name')
            ->get();

        $assignableRoles = Role::where('guard_name', 'web')
            ->whereNotIn('name', self::SYSTEM_ROLES)
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                $role->label = self::ROLE_LABELS[$role->name] ?? Str::title(str_replace('_', ' ', $role->name));
                return $role;
            });

        return view('roles.index', compact('roles', 'staffUsers', 'assignableRoles'));
    }

    public function create()
    {
        $groupedPermissions = $this->groupedPermissions();

        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('roles', 'name')],
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.regex' => 'El nombre solo puede contener letras minusculas, numeros y guiones bajos.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
            'permissions.required' => 'Debes seleccionar al menos un permiso.',
            'permissions.min' => 'Debes seleccionar al menos un permiso.',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        abort_if(in_array($role->name, self::SYSTEM_ROLES), 403, 'Los roles de doctor no se pueden editar desde aqui.');

        $groupedPermissions = $this->groupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $role->label = self::ROLE_LABELS[$role->name] ?? Str::title(str_replace('_', ' ', $role->name));
        $role->is_protected = in_array($role->name, self::PROTECTED_ROLES);
        $usersCount = $role->users()->count();

        return view('roles.edit', compact('role', 'groupedPermissions', 'rolePermissions', 'usersCount'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if(in_array($role->name, self::SYSTEM_ROLES), 403);

        $rules = [
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|exists:permissions,name',
        ];

        // Only custom roles can change their name
        if (!in_array($role->name, self::PROTECTED_ROLES)) {
            $rules['name'] = ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('roles', 'name')->ignore($role->id)];
        }

        $validated = $request->validate($rules, [
            'name.regex' => 'El nombre solo puede contener letras minusculas, numeros y guiones bajos.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
            'permissions.required' => 'Debes seleccionar al menos un permiso.',
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        $role->syncPermissions($validated['permissions']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Role $role)
    {
        abort_if(in_array($role->name, self::PROTECTED_ROLES), 403, 'Este rol no se puede eliminar.');

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'No se puede eliminar un rol que tiene usuarios asignados. Reasigna los usuarios primero.');
        }

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }

    public function assignRole(Request $request, User $user)
    {
        abort_if($user->isDoctor(), 403, 'No se puede cambiar el rol de un doctor desde aqui.');

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ], [
            'role.required' => 'Debes seleccionar un rol.',
            'role.exists' => 'El rol seleccionado no existe.',
        ]);

        // Prevent assigning doctor roles to staff
        abort_if(in_array($validated['role'], self::SYSTEM_ROLES), 403);

        $user->syncRoles([$validated['role']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', "Rol de {$user->name} actualizado exitosamente.");
    }

    /**
     * Group all permissions by module prefix for display.
     */
    private function groupedPermissions(): array
    {
        return Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($p) => Str::before($p->name, '.'))
            ->map(fn ($perms, $module) => [
                'module' => $module,
                'label' => self::PERMISSION_MODULES[$module] ?? Str::title($module),
                'permissions' => $perms->map(fn ($p) => [
                    'name' => $p->name,
                    'label' => self::PERMISSION_LABELS[$p->name] ?? $p->name,
                ]),
            ])
            ->values()
            ->all();
    }
}
