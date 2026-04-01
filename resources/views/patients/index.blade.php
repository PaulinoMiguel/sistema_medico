<x-layouts.tenant :title="'Pacientes'">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pacientes</h2>
        <a href="{{ route('patients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Nuevo paciente
        </a>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <form method="GET" action="{{ route('patients.index') }}">
                <div class="flex gap-4">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre, apellido, documento o expediente..."
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">Buscar</button>
                    @if($search)
                        <a href="{{ route('patients.index') }}" class="text-gray-500 px-4 py-2 hover:text-gray-700">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Patient List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($patients->isEmpty())
            <div class="p-6 text-center text-gray-500">
                {{ $search ? 'No se encontraron pacientes con esa busqueda.' : 'No hay pacientes registrados.' }}
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expediente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Edad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seguro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($patients as $patient)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ $patient->medical_record_number }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('patients.show', $patient) }}" class="text-sm font-medium text-blue-600 hover:underline">
                                {{ $patient->full_name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $patient->age }} anios</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $patient->phone ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $patient->insurance_provider ?? 'Particular' }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('patients.show', $patient) }}" class="text-blue-600 hover:underline">Ver</a>
                            <a href="{{ route('patients.edit', $patient) }}" class="text-gray-600 hover:underline">Editar</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</x-layouts.tenant>
