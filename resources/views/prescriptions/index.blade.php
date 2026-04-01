<x-layouts.tenant title="Recetas">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Recetas</h2>
        <a href="{{ route('prescriptions.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
            + Nueva Receta
        </a>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <form method="GET" class="flex gap-4">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por nombre de paciente..."
                       class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-200">Buscar</button>
                @if($search)
                    <a href="{{ route('prescriptions.index') }}" class="text-gray-500 px-3 py-2 text-sm hover:text-gray-700">Limpiar</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Receta</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicamentos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($prescriptions as $prescription)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $prescription->prescription_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $prescription->patient->full_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $prescription->prescription_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $prescription->items->count() }} medicamento(s)</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'active' => 'background-color:#dcfce7;color:#166534;',
                                    'expired' => 'background-color:#fef3c7;color:#92400e;',
                                    'cancelled' => 'background-color:#fee2e2;color:#991b1b;',
                                ];
                                $statusLabels = ['active' => 'Activa', 'expired' => 'Vencida', 'cancelled' => 'Cancelada'];
                            @endphp
                            <span style="{{ $statusColors[$prescription->status] }}" class="px-2 py-1 text-xs font-semibold rounded-full">
                                {{ $statusLabels[$prescription->status] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('prescriptions.show', $prescription) }}" class="text-blue-600 hover:underline text-sm">Ver</a>
                            <a href="{{ route('prescriptions.edit', $prescription) }}" class="text-gray-600 hover:underline text-sm">Editar</a>
                            <a href="{{ route('prescriptions.pdf', $prescription) }}" target="_blank" class="text-green-600 hover:underline text-sm">PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No se encontraron recetas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $prescriptions->links() }}
    </div>
</x-layouts.tenant>
