<x-layouts.tenant :title="'Historial - ' . $patient->full_name">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $patient->full_name }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Expediente: {{ $patient->medical_record_number }} |
            {{ $patient->age }} años |
            {{ $patient->gender == 'male' ? 'Masculino' : ($patient->gender == 'female' ? 'Femenino' : 'Otro') }}
            @if($patient->blood_type) | Sangre: {{ $patient->blood_type }} @endif
        </p>
    </div>

    {{-- Consultas previas --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Consultas previas</h3>
        </div>
        @if($consultations->isEmpty())
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                Sin consultas registradas.
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($consultations as $consultation)
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $consultation->consultation_date->format('d/m/Y') }}
                                — Dr. {{ $consultation->doctor->name }}
                            </p>
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full {{ $consultation->status === 'signed' ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300' }}">
                                {{ $consultation->status === 'signed' ? 'Firmada' : 'En progreso' }}
                            </span>
                        </div>
                    </div>

                    @if($consultation->reason)
                        <div class="mb-2">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Motivo:</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $consultation->reason }}</p>
                        </div>
                    @endif

                    @php $dx = $consultation->diagnoses; @endphp
                    @if(!empty($dx))
                        <div class="mb-2">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Diagnosticos:</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach((array)$dx as $d)
                                    <span class="inline-block px-2 py-0.5 text-xs bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded">
                                        {{ is_array($d) ? ($d['description'] ?? ($d['code'] ?? json_encode($d))) : $d }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($consultation->subjective)
                        <div class="mb-1">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subjetivo:</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($consultation->subjective, 200) }}</p>
                        </div>
                    @endif

                    @if($consultation->plan)
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan:</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($consultation->plan, 200) }}</p>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recetas previas --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Recetas previas</h3>
        </div>
        @if($prescriptions->isEmpty())
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                Sin recetas registradas.
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($prescriptions as $rx)
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $rx->prescription_number }} — {{ $rx->prescription_date->format('d/m/Y') }}
                        </p>
                        @php
                            $rxStatusColors = ['active'=>'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300','expired'=>'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300','cancelled'=>'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300'];
                            $rxStatusLabels = ['active'=>'Activa','expired'=>'Vencida','cancelled'=>'Cancelada'];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 text-xs rounded-full {{ $rxStatusColors[$rx->status] ?? '' }}">
                            {{ $rxStatusLabels[$rx->status] ?? $rx->status }}
                        </span>
                    </div>
                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        @foreach($rx->items as $item)
                            <li>{{ $item->medication_name }} — {{ $item->dosage }} {{ $item->frequency ? '/ '.$item->frequency : '' }}</li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Antecedentes medicos --}}
    @php
        $mh = $patient->medicalHistory;
        $hasBackground = $mh && (
            !empty($mh->allergies) || !empty($mh->chronic_conditions) || !empty($mh->current_medications) ||
            !empty($mh->surgical_history) || !empty($mh->family_history) || !empty($mh->habits) ||
            !empty($mh->urological_history) || !empty($mh->obstetric_gynecological) || !empty($mh->immunizations)
        );
    @endphp

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Antecedentes medicos</h3>
        </div>
        @if(!$hasBackground)
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                Sin antecedentes medicos registrados.
            </div>
        @else
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                @php
                    $sections = [
                        'allergies' => 'Alergias',
                        'chronic_conditions' => 'Condiciones cronicas',
                        'current_medications' => 'Medicamentos actuales',
                        'surgical_history' => 'Historial quirurgico',
                        'family_history' => 'Historial familiar',
                        'habits' => 'Habitos',
                        'urological_history' => 'Historial urologico',
                        'obstetric_gynecological' => 'Gineco-obstetrico',
                        'immunizations' => 'Inmunizaciones',
                    ];
                @endphp
                @foreach($sections as $field => $label)
                    @if(!empty($mh->$field))
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ $label }}</h4>
                        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            @foreach((array)$mh->$field as $item)
                                <li>{{ is_array($item) ? ($item['name'] ?? ($item['description'] ?? json_encode($item))) : $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.tenant>
