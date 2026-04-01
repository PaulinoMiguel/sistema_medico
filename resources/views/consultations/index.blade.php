<x-layouts.tenant :title="'Consultas'">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Consultas</h2>
        <a href="{{ route('consultations.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Nueva consulta
        </a>
    </div>

    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="{{ route('consultations.index') }}" class="flex gap-4">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre del paciente..."
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">Buscar</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($consultations->isEmpty())
            <div class="p-6 text-center text-gray-500">No hay consultas registradas.</div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $typeLabels = ['initial'=>'Inicial','follow_up'=>'Control','pre_operative'=>'Pre-Qx','post_operative'=>'Post-Qx','emergency'=>'Urgencia','urodynamic'=>'Urodinamia','procedure'=>'Procedimiento'];
                    @endphp
                    @foreach($consultations as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-800">{{ $c->consultation_date->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('patients.show', $c->patient) }}" class="text-blue-600 hover:underline">{{ $c->patient->full_name }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $typeLabels[$c->type] ?? $c->type }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">{{ $c->chief_complaint ?? '-' }}</td>
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
                            @else
                                <a href="{{ route('consultations.edit', $c) }}" class="text-blue-600 hover:underline">Continuar</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">{{ $consultations->links() }}</div>
        @endif
    </div>
</x-layouts.tenant>
