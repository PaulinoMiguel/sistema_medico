<x-layouts.tenant :title="'Procedimientos'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Procedimientos</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Catálogo de procedimientos/estudios. Entra a cada uno para fijar el código, simón y monto por aseguradora.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            {{-- Create --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Nuevo procedimiento</h3>
                <form method="POST" action="{{ route('procedures.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: Circuncisión, Vasectomía...">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        Crear procedimiento
                    </button>
                </form>
            </div>

            {{-- Import CSV --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Importar CSV</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Columnas: <code>PROCEDIMIENTO, ARS, CODIGO, SIMON, MONTO</code> (una fila por aseguradora).
                    Crea/actualiza procedimientos, aseguradoras y sus códigos.
                </p>
                <form method="POST" action="{{ route('procedures.import') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="csv" accept=".csv,text/csv" required
                           class="block w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md file:bg-blue-50 file:border-0 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700">
                    @error('csv') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <button type="submit" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 font-medium">
                        Importar
                    </button>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                @if($procedures->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <p>No hay procedimientos.</p>
                        <p class="text-sm mt-2">Créalos manualmente o importa el CSV del catálogo.</p>
                    </div>
                @else
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Procedimiento</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ARS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($procedures as $procedure)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 {{ !$procedure->is_active ? 'opacity-50' : '' }}" x-data="{ edit: false }">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <span x-show="!edit">{{ $procedure->name }}</span>
                                    <form x-show="edit" x-cloak method="POST" action="{{ route('procedures.update', $procedure) }}" class="flex gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $procedure->name }}" required
                                               class="px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md text-sm w-full">
                                        <button class="text-blue-600 hover:underline text-sm">Guardar</button>
                                        <button type="button" @click="edit=false" class="text-gray-400 hover:underline text-sm">Cancelar</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-right">{{ $procedure->insurers_count }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $procedure->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $procedure->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-3 whitespace-nowrap">
                                    <a href="{{ route('procedures.show', $procedure) }}" class="text-blue-600 hover:underline">Códigos</a>
                                    <button type="button" @click="edit=!edit" class="text-gray-600 dark:text-gray-300 hover:underline">Editar</button>
                                    <form action="{{ route('procedures.toggle', $procedure) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="{{ $procedure->is_active ? 'text-red-600' : 'text-green-600' }} hover:underline">
                                            {{ $procedure->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('procedures.destroy', $procedure) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Eliminar el procedimiento y sus códigos? Los documentos ya guardados no se afectan.')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-layouts.tenant>
