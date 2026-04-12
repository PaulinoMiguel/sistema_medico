<x-layouts.tenant :title="'Receta - ' . $prescription->prescription_number">
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('prescriptions.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a recetas</a>
        <div class="space-x-2">
            <a href="{{ route('prescriptions.edit', $prescription) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-200">Editar</a>
            <a href="{{ route('prescriptions.pdf', $prescription) }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">Imprimir PDF</a>
        </div>
    </div>

    @php
        $statusColors = [
            'active' => 'background-color:#dcfce7;color:#166534;',
            'expired' => 'background-color:#fef3c7;color:#92400e;',
            'cancelled' => 'background-color:#fee2e2;color:#991b1b;',
        ];
        $statusLabels = ['active' => 'Activa', 'expired' => 'Vencida', 'cancelled' => 'Cancelada'];
    @endphp

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $prescription->patient->full_name }}</h2>
                    <p class="text-gray-500 text-sm mt-1">
                        Receta {{ $prescription->prescription_number }} |
                        {{ $prescription->prescription_date->format('d/m/Y') }} |
                        Dr. {{ $prescription->doctor->name }}
                    </p>
                    @if($prescription->consultation)
                        <p class="text-gray-400 text-xs mt-1">
                            Consulta del {{ $prescription->consultation->consultation_date->format('d/m/Y') }}
                        </p>
                    @endif
                </div>
                <span style="{{ $statusColors[$prescription->status] }}" class="px-3 py-1 text-sm font-semibold rounded-full">
                    {{ $statusLabels[$prescription->status] }}
                </span>
            </div>

        </div>
    </div>

    {{-- Medications --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div style="background-color:#dbeafe;" class="px-6 py-3 rounded-t-lg border-b">
            <h3 class="font-semibold text-gray-800">Medicamentos</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($prescription->items as $index => $item)
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-semibold text-gray-800">{{ $index + 1 }}. {{ $item->medication_name }}</h4>
                            <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Dosis:</span>
                                    <span class="text-gray-800">{{ $item->dosage }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Frecuencia:</span>
                                    <span class="text-gray-800">{{ $item->frequency }}</span>
                                </div>
                                @if($item->duration)
                                <div>
                                    <span class="text-gray-500">Duracion:</span>
                                    <span class="text-gray-800">{{ $item->duration }}</span>
                                </div>
                                @endif
                                <div>
                                    <span class="text-gray-500">Via:</span>
                                    @php
                                        $routeLabels = ['oral'=>'Oral','sublingual'=>'Sublingual','topical'=>'Topica','intramuscular'=>'Intramuscular','intravenous'=>'Intravenosa','rectal'=>'Rectal','ophthalmic'=>'Oftalmica','otic'=>'Otica','nasal'=>'Nasal','inhaled'=>'Inhalada'];
                                    @endphp
                                    <span class="text-gray-800">{{ $routeLabels[$item->route] ?? $item->route }}</span>
                                </div>
                            </div>
                            @if($item->quantity)
                                <p class="text-sm text-gray-600 mt-1">Cantidad: {{ $item->quantity }}</p>
                            @endif
                            @if($item->instructions)
                                <p class="text-sm text-gray-600 mt-2 italic">{{ $item->instructions }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Notes --}}
    @if($prescription->notes)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-3 border-b bg-gray-50 rounded-t-lg">
                <h3 class="font-semibold text-gray-800">Notas</h3>
            </div>
            <div class="p-6 text-sm text-gray-600">
                {{ $prescription->notes }}
            </div>
        </div>
    @endif
</x-layouts.tenant>
