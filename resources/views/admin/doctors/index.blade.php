<x-layouts.admin :title="'Doctores'">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-white">Doctores</h2>
        <a href="{{ route('admin.doctors.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Nuevo doctor
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
        @if($doctors->isEmpty())
            <div class="p-8 text-center text-gray-400">
                <p class="mb-4">No hay doctores registrados.</p>
                <a href="{{ route('admin.doctors.create') }}" class="text-blue-400 hover:underline">Crear el primer doctor</a>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Especialidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Clinica(s)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($doctors as $doctor)
                    @php
                        $statusClasses = match ($doctor->status) {
                            'active' => 'bg-green-900/50 text-green-300',
                            'passive' => 'bg-yellow-900/50 text-yellow-300',
                            'inactive' => 'bg-red-900/50 text-red-300',
                        };
                    @endphp
                    <tr class="hover:bg-gray-750 {{ !$doctor->isActive() ? 'opacity-50' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-white">{{ $doctor->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-300">{{ $doctor->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-300">{{ $doctor->specialty ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-300">
                            @forelse($doctor->clinics as $clinic)
                                <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-blue-900/50 text-blue-300 mr-1">
                                    {{ $clinic->name }}
                                </span>
                            @empty
                                <span class="text-gray-500">Sin clinicas</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses }}">
                                {{ $doctor->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.doctors.edit', $doctor) }}" class="text-blue-400 hover:underline">Editar</a>
                            <form action="{{ route('admin.doctors.toggle', $doctor) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="{{ $doctor->isActive() ? 'text-red-400' : 'text-green-400' }} hover:underline">
                                    {{ $doctor->isActive() ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.admin>
