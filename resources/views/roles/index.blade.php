<x-layouts.tenant :title="'Roles y Permisos'">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Roles y Permisos</h2>
        <a href="{{ route('roles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Crear rol personalizado
        </a>
    </div>

    {{-- Error flash (for delete failures, etc.) --}}
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    {{-- Roles table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Roles del sistema</h3>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Permisos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Usuarios</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($roles as $role)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $role->label }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $role->name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300">
                            {{ $role->permissions_count }} permisos
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $role->users_count }} {{ $role->users_count === 1 ? 'usuario' : 'usuarios' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($role->is_system)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300">
                                Sistema
                            </span>
                        @elseif($role->is_protected)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300">
                                Editable
                            </span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">
                                Personalizado
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        @if(!$role->is_system)
                            <a href="{{ route('roles.edit', $role) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                        @endif
                        @if(!$role->is_protected)
                            @if($role->users_count === 0)
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Eliminar el rol {{ $role->label }}?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 dark:text-red-400 hover:underline">Eliminar</button>
                                </form>
                            @else
                                <span class="text-gray-400 dark:text-gray-500 text-xs" title="Reasigna los usuarios antes de eliminar">No eliminable</span>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Staff role assignment --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Asignar roles al personal</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Cambia el rol de secretarias y enfermeras. Los roles de doctor se gestionan desde el panel de super admin.</p>
        </div>
        @if($staffUsers->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                No hay personal registrado.
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Clinica(s)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rol actual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cambiar rol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($staffUsers as $user)
                    @php $currentRole = $user->roles->first(); @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            @foreach($user->clinics as $clinic)
                                <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 mr-1">
                                    {{ $clinic->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-300">
                                {{ $currentRole ? $user->role_label : 'Sin rol' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('users.assign-role', $user) }}" method="POST" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <select name="role" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($assignableRoles as $assignableRole)
                                        <option value="{{ $assignableRole->name }}" {{ $currentRole && $currentRole->name === $assignableRole->name ? 'selected' : '' }}>
                                            {{ $assignableRole->label }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 font-medium">
                                    Cambiar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.tenant>
