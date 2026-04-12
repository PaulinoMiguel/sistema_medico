<x-layouts.tenant :title="'Editar Rol: ' . $role->label">
    <div class="mb-6">
        <a href="{{ route('roles.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">&larr; Volver a roles</a>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-2">Editar rol: {{ $role->label }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $usersCount }} {{ $usersCount === 1 ? 'usuario tiene' : 'usuarios tienen' }} este rol asignado.
        </p>
    </div>

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')

        {{-- Role name --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Nombre del rol</h3>
            <div class="max-w-md">
                @if($role->is_protected)
                    <input type="text" value="{{ $role->name }}" disabled
                           class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-md">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">El nombre de los roles predefinidos no se puede cambiar.</p>
                @else
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           pattern="[a-z][a-z0-9_]*">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Solo letras minusculas, numeros y guiones bajos.</p>
                @endif
            </div>
        </div>

        {{-- Permissions by module --}}
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Permisos</h3>
                <button type="button" id="toggle-all" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    Seleccionar todo
                </button>
            </div>

            @php $oldPerms = old('permissions', $rolePermissions); @endphp

            @foreach($groupedPermissions as $group)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-700 dark:text-gray-300">{{ $group['label'] }}</h4>
                        <button type="button" class="toggle-group text-xs text-blue-600 dark:text-blue-400 hover:underline" data-toggle-group="{{ $group['module'] }}">
                            Seleccionar todo
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($group['permissions'] as $permission)
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $permission['name'] }}"
                                       data-group="{{ $group['module'] }}"
                                       class="perm-checkbox rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                       {{ in_array($permission['name'], $oldPerms) ? 'checked' : '' }}>
                                {{ $permission['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                Guardar cambios
            </button>
            <a href="{{ route('roles.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium">
                Cancelar
            </a>
        </div>
    </form>

    <script>
        document.querySelectorAll('[data-toggle-group]').forEach(btn => {
            btn.addEventListener('click', () => {
                const group = btn.dataset.toggleGroup;
                const boxes = document.querySelectorAll(`[data-group="${group}"]`);
                const allChecked = [...boxes].every(cb => cb.checked);
                boxes.forEach(cb => cb.checked = !allChecked);
                btn.textContent = allChecked ? 'Seleccionar todo' : 'Deseleccionar todo';
            });
        });

        document.getElementById('toggle-all').addEventListener('click', function () {
            const boxes = document.querySelectorAll('.perm-checkbox');
            const allChecked = [...boxes].every(cb => cb.checked);
            boxes.forEach(cb => cb.checked = !allChecked);
            this.textContent = allChecked ? 'Seleccionar todo' : 'Deseleccionar todo';
        });
    </script>
</x-layouts.tenant>
