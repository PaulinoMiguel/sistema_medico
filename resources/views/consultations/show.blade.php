<x-layouts.tenant :title="'Consulta - ' . $consultation->patient->full_name">
    @php
        $templateSlug = $consultation->consultation_template;
        if (!$templateSlug) {
            $sk = strtolower(str_replace(' ', '_', $consultation->doctor->specialty ?? 'general'));
            $sm = ['urologia' => 'urology', 'pediatria' => 'pediatrics', 'neurologia' => 'neurology', 'medicina_general' => 'general'];
            $templateSlug = ($sm[$sk] ?? $sk) . '_generic';
        }
        $templateConfig = config("consultation_templates.{$templateSlug}");
        $specialtyKey = $templateConfig['specialty'] ?? 'general';
        $specialtyConfig = config("specialties.{$specialtyKey}", config('specialties.general'));
        $typeLabels = $specialtyConfig['consultation_types'];
        $vs = $consultation->vital_signs ?? [];
        $us = $consultation->urinary_symptoms ?? [];
        $sf = $consultation->sexual_function ?? [];
        $subjectivePartial = \App\Support\ConsultationTemplate::resolvePartial($templateSlug, 'show-subjective');
        $objectivePartial = \App\Support\ConsultationTemplate::resolvePartial($templateSlug, 'show-objective');
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
        <div class="flex items-center gap-2">
            <form action="{{ route('prescriptions.from-consultation', $consultation) }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700">
                    + Receta
                </button>
            </form>
            <span style="background-color:#dcfce7;color:#166534;" class="px-3 py-1 text-sm font-semibold rounded-full">
                {{ $consultation->status === 'signed' ? 'Firmada' : 'En progreso' }}
            </span>
        </div>
    </div>

    @if($consultation->type !== 'initial')
        <div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
            <h3 class="font-semibold text-gray-800 mb-3">Notas de la consulta</h3>
            @if($consultation->notes)
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $consultation->notes }}</p>
            @else
                <p class="text-sm text-gray-400 italic">Sin notas registradas.</p>
            @endif
        </div>
    @else
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
                {{-- Specialty-specific subjective data --}}
                @includeIf($subjectivePartial)
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
                        @foreach(['blood_pressure'=>'PA','heart_rate'=>'FC','temperature'=>'Temp','weight'=>'Peso','height'=>'Talla','respiratory_rate'=>'FR'] as $k => $l)
                            @if(!empty($vs[$k]))
                                <span class="text-gray-600"><span class="font-medium">{{ $l }}:</span> {{ $vs[$k] }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
                @if($consultation->abdomen_exam) <div><span class="font-medium text-gray-700">Abdomen:</span> {{ $consultation->abdomen_exam }}</div> @endif
                {{-- Specialty-specific objective data --}}
                @includeIf($objectivePartial)
            </div>
        </div>

        {{-- A --}}
        <div class="bg-white rounded-lg shadow">
            <div style="background-color:#fef3c7;" class="px-6 py-3 rounded-t-lg border-b">
                <h3 class="font-semibold text-gray-800">A - Evaluacion</h3>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @if($consultation->assessment) <div>{{ $consultation->assessment }}</div> @endif
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
                @if($consultation->follow_up_days) <div><span class="font-medium text-gray-700">Proxima cita:</span> {{ $consultation->follow_up_days }} dias</div> @endif
                @if($consultation->referrals) <div><span class="font-medium text-gray-700">Referencias:</span> {{ $consultation->referrals }}</div> @endif
            </div>
        </div>
    </div>
    @endif
</x-layouts.tenant>
