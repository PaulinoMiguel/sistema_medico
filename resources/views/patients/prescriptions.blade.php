<x-layouts.tenant :title="'Recetas - ' . $patient->full_name">
    @include('patients.partials.header-tabs')

    {{-- ===== Pestaña: Recetas ===== --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Recetas</h3>
        @can('prescriptions.create')
        <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}"
           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">+ Nueva receta</a>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        @if($prescriptions->isEmpty())
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">Sin recetas registradas.</div>
        @else
            @php
                $rxStatusColors = ['active'=>'bg-green-100 text-green-800','expired'=>'bg-yellow-100 text-yellow-800','cancelled'=>'bg-red-100 text-red-800'];
                $rxStatusLabels = ['active'=>'Activa','expired'=>'Vencida','cancelled'=>'Cancelada'];
            @endphp
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Número</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Medicamentos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($prescriptions as $rx)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $rx->prescription_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $rx->prescription_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $rx->items->count() }} medicamento(s)</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $rxStatusColors[$rx->status] ?? '' }}">
                                {{ $rxStatusLabels[$rx->status] ?? $rx->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('prescriptions.show', $rx) }}" class="text-blue-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.tenant>
