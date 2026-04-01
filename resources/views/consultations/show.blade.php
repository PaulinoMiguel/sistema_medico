<x-layouts.tenant :title="'Consulta - ' . $consultation->patient->full_name">
    @php
        $typeLabels = ['initial'=>'Consulta inicial','follow_up'=>'Control','pre_operative'=>'Pre-quirurgico','post_operative'=>'Post-quirurgico','emergency'=>'Urgencia','urodynamic'=>'Urodinamia','procedure'=>'Procedimiento'];
        $vs = $consultation->vital_signs ?? [];
        $us = $consultation->urinary_symptoms ?? [];
        $sf = $consultation->sexual_function ?? [];
        $dx = $consultation->diagnoses ?? [];
    @endphp

    <div class="mb-6">
        <a href="{{ route('consultations.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a consultas</a>
    </div>

    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $consultation->patient->full_name }}</h2>
            <p class="text-gray-500 text-sm">
                {{ $typeLabels[$consultation->type] ?? $consultation->type }} |
                {{ $consultation->consultation_date->format('d/m/Y H:i') }}
                @if($consultation->signed_at)
                    | Firmada: {{ $consultation->signed_at->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
        <span style="background-color:#dcfce7;color:#166534;" class="px-3 py-1 text-sm font-semibold rounded-full">
            {{ $consultation->status === 'signed' ? 'Firmada' : 'En progreso' }}
        </span>
    </div>

    <div class="space-y-6">
        {{-- S --}}
        <div class="bg-white rounded-lg shadow">
            <div style="background-color:#dbeafe;" class="px-6 py-3 rounded-t-lg border-b">
                <h3 class="font-semibold text-gray-800">S - Subjetivo</h3>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @if($consultation->chief_complaint)
                    <div><span class="font-medium text-gray-700">Motivo de consulta:</span> {{ $consultation->chief_complaint }}</div>
                @endif
                @if($consultation->history_present_illness)
                    <div><span class="font-medium text-gray-700">Historia de la enfermedad:</span> {{ $consultation->history_present_illness }}</div>
                @endif
                @if(!empty($us))
                    <div>
                        <span class="font-medium text-gray-700">Sintomas urinarios:</span>
                        @foreach(['frequency'=>'Frecuencia','urgency'=>'Urgencia','nocturia'=>'Nocturia','weak_stream'=>'Chorro debil','intermittency'=>'Intermitencia','straining'=>'Esfuerzo','incomplete_emptying'=>'Vaciado incompleto','hematuria'=>'Hematuria','dysuria'=>'Disuria','incontinence'=>'Incontinencia'] as $k => $l)
                            @if(!empty($us[$k])) <span style="background-color:#dbeafe;color:#1e40af;" class="inline-block px-2 py-0.5 rounded text-xs mr-1 mb-1">{{ $l }}</span> @endif
                        @endforeach
                        @if(!empty($us['ipss_score'])) <span class="ml-2 text-gray-600">IPSS: {{ $us['ipss_score'] }}</span> @endif
                    </div>
                @endif
                @if(!empty($sf))
                    <div>
                        <span class="font-medium text-gray-700">Funcion sexual:</span>
                        @foreach(['erectile_dysfunction'=>'Disf. erectil','decreased_libido'=>'Libido baja','premature_ejaculation'=>'Eyac. precoz','painful_ejaculation'=>'Eyac. dolorosa','hematospermia'=>'Hematospermia'] as $k => $l)
                            @if(!empty($sf[$k])) <span style="background-color:#fce7f3;color:#9d174d;" class="inline-block px-2 py-0.5 rounded text-xs mr-1 mb-1">{{ $l }}</span> @endif
                        @endforeach
                        @if(!empty($sf['iief_score'])) <span class="ml-2 text-gray-600">IIEF: {{ $sf['iief_score'] }}</span> @endif
                    </div>
                @endif
                @if($consultation->review_of_systems)
                    <div><span class="font-medium text-gray-700">Revision por sistemas:</span> {{ $consultation->review_of_systems }}</div>
                @endif
            </div>
        </div>

        {{-- O --}}
        <div class="bg-white rounded-lg shadow">
            <div style="background-color:#dcfce7;" class="px-6 py-3 rounded-t-lg border-b">
                <h3 class="font-semibold text-gray-800">O - Objetivo</h3>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @if(!empty($vs))
                    <div class="flex flex-wrap gap-4">
                        @foreach(['blood_pressure'=>'PA','heart_rate'=>'FC','temperature'=>'Temp','weight'=>'Peso','height'=>'Talla','oxygen_saturation'=>'SpO2','respiratory_rate'=>'FR'] as $k => $l)
                            @if(!empty($vs[$k]))
                                <span class="text-gray-600"><span class="font-medium">{{ $l }}:</span> {{ $vs[$k] }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if($consultation->physical_exam) <div><span class="font-medium text-gray-700">Examen fisico:</span> {{ $consultation->physical_exam }}</div> @endif
                @if($consultation->abdomen_exam) <div><span class="font-medium text-gray-700">Abdomen:</span> {{ $consultation->abdomen_exam }}</div> @endif
                @if($consultation->genitourinary_exam) <div><span class="font-medium text-gray-700">Genitourinario:</span> {{ $consultation->genitourinary_exam }}</div> @endif
                @if($consultation->rectal_exam) <div><span class="font-medium text-gray-700">Tacto rectal:</span> {{ $consultation->rectal_exam }}</div> @endif
            </div>
        </div>

        {{-- A --}}
        <div class="bg-white rounded-lg shadow">
            <div style="background-color:#fef3c7;" class="px-6 py-3 rounded-t-lg border-b">
                <h3 class="font-semibold text-gray-800">A - Evaluacion</h3>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @if($consultation->assessment) <div>{{ $consultation->assessment }}</div> @endif
                @if(!empty($dx))
                    <div class="mt-2">
                        @foreach($dx as $d)
                            @if(!empty($d['code']) || !empty($d['description']))
                                <div class="flex items-center gap-2 py-1">
                                    @if(!empty($d['code'])) <span style="background-color:#f3f4f6;" class="px-2 py-0.5 rounded text-xs font-mono">{{ $d['code'] }}</span> @endif
                                    <span>{{ $d['description'] ?? '' }}</span>
                                    <span class="text-xs text-gray-400">({{ ($d['type'] ?? 'primary') === 'primary' ? 'Principal' : 'Secundario' }})</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- P --}}
        <div class="bg-white rounded-lg shadow">
            <div style="background-color:#fce7f3;" class="px-6 py-3 rounded-t-lg border-b">
                <h3 class="font-semibold text-gray-800">P - Plan</h3>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @if($consultation->treatment_plan) <div><span class="font-medium text-gray-700">Tratamiento:</span> {{ $consultation->treatment_plan }}</div> @endif
                @if($consultation->diagnostic_orders) <div><span class="font-medium text-gray-700">Ordenes:</span> {{ $consultation->diagnostic_orders }}</div> @endif
                @if($consultation->surgical_recommendation) <div><span class="font-medium text-gray-700">Recomendacion quirurgica:</span> {{ $consultation->surgical_recommendation }}</div> @endif
                @if($consultation->follow_up_instructions) <div><span class="font-medium text-gray-700">Seguimiento:</span> {{ $consultation->follow_up_instructions }}</div> @endif
                @if($consultation->follow_up_days) <div><span class="font-medium text-gray-700">Proxima cita:</span> {{ $consultation->follow_up_days }} dias</div> @endif
                @if($consultation->referrals) <div><span class="font-medium text-gray-700">Referencias:</span> {{ $consultation->referrals }}</div> @endif
            </div>
        </div>
    </div>
</x-layouts.tenant>
