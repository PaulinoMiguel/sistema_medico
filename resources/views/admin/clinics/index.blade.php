<x-layouts.admin :title="'Clinicas'">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-white">Clinicas</h2>
        <a href="{{ route('admin.clinics.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Nueva clinica
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
        @if($clinics->isEmpty())
            <div class="p-8 text-center text-gray-400">
                <p class="mb-4">No hay clinicas registradas.</p>
                <a href="{{ route('admin.clinics.create') }}" class="text-blue-400 hover:underline">Crear la primera clinica</a>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Doctores</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Pacientes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($clinics as $clinic)
                    <tr class="hover:bg-gray-750 {{ !$clinic->is_active ? 'opacity-50' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-white">{{ $clinic->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-300">{{ $clinic->users_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-300">{{ $clinic->patients_count }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $clinic->is_active ? 'bg-green-900/50 text-green-300' : 'bg-red-900/50 text-red-300' }}">
                                {{ $clinic->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.clinics.edit', $clinic) }}" class="text-blue-400 hover:underline">Editar</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.admin>
