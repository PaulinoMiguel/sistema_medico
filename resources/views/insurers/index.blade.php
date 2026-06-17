<x-layouts.tenant :title="'Aseguradoras y códigos'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Aseguradoras y códigos</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Catálogo de aseguradoras (ARS). Entra a cada una para gestionar sus códigos.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Create form --}}
        <div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Nueva aseguradora</h3>
                <form method="POST" action="{{ route('insurers.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: ARS Humano, Senasa, Mapfre...">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        Crear aseguradora
                    </button>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                @if($insurers->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <p>No hay aseguradoras creadas.</p>
                        <p class="text-sm mt-2">Crea las ARS con las que trabajas: Humano, Senasa, Mapfre, etc.</p>
                    </div>
                @else
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aseguradora</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Procedimientos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($insurers as $insurer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 {{ !$insurer->is_active ? 'opacity-50' : '' }}" x-data="{ edit: false }">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <span x-show="!edit">{{ $insurer->name }}</span>
                                    <form x-show="edit" x-cloak method="POST" action="{{ route('insurers.update', $insurer) }}" class="flex gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $insurer->name }}" required
                                               class="px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md text-sm">
                                        <button class="text-blue-600 hover:underline text-sm">Guardar</button>
                                        <button type="button" @click="edit=false" class="text-gray-400 hover:underline text-sm">Cancelar</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 text-right">{{ $insurer->procedures_count }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $insurer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $insurer->is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-3 whitespace-nowrap">
                                    <button type="button" @click="edit=!edit" class="text-gray-600 dark:text-gray-300 hover:underline">Editar</button>
                                    <form action="{{ route('insurers.toggle', $insurer) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="{{ $insurer->is_active ? 'text-red-600' : 'text-green-600' }} hover:underline">
                                            {{ $insurer->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('insurers.destroy', $insurer) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Eliminar la aseguradora y todos sus códigos? Los documentos ya guardados no se afectan.')">
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
