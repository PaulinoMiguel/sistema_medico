<x-layouts.tenant :title="'Consultas - ' . $patient->full_name">
    @include('patients.partials.header-tabs')

    {{-- ===== Pestaña: Consultas ===== --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Consultas</h3>
        @can('consultations.create')
        <a href="{{ route('consultations.create', ['patient_id' => $patient->id]) }}"
           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">+ Nueva consulta</a>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        @if($consultations->isEmpty())
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">Sin consultas registradas.</div>
        @else
            @php
                $typeLabels = ['initial'=>'Inicial','follow_up'=>'Control','pre_operative'=>'Pre-Qx','post_operative'=>'Post-Qx','emergency'=>'Urgencia','urodynamic'=>'Urodinamia','flowmetry'=>'Flujometría','procedure'=>'Procedimiento'];
            @endphp
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Motivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($consultations as $c)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-6 py-4 text-sm text-gray-800 dark:text-gray-200">{{ $c->consultation_date->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $typeLabels[$c->type] ?? $c->type }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $c->chief_complaint ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($c->status === 'signed')
                                <span style="background-color:#dcfce7;color:#166534;" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">Firmada</span>
                            @else
                                <span style="background-color:#dbeafe;color:#1e40af;" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">En progreso</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($c->status === 'signed')
                                <a href="{{ route('consultations.show', $c) }}" class="text-blue-600 hover:underline">Ver</a>
                                @can('consultations.create')
                                <a href="{{ route('consultations.edit', $c) }}" class="text-blue-600 hover:underline ml-3">Editar</a>
                                @endcan
                            @else
                                <a href="{{ route('consultations.edit', $c) }}" class="text-blue-600 hover:underline">Continuar</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.tenant>
