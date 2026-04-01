<x-layouts.tenant :title="'Consulta - ' . $consultation->patient->full_name">
    @php
        $typeLabels = ['initial'=>'Consulta inicial','follow_up'=>'Control','pre_operative'=>'Pre-quirurgico','post_operative'=>'Post-quirurgico','emergency'=>'Urgencia','urodynamic'=>'Urodinamia','procedure'=>'Procedimiento'];
        $vs = $consultation->vital_signs ?? [];
        $us = $consultation->urinary_symptoms ?? [];
        $sf = $consultation->sexual_function ?? [];
        $dx = $consultation->diagnoses ?? [];
    @endphp

    {{-- Header --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <a href="{{ route('consultations.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a consultas</a>
            <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $consultation->patient->full_name }}</h2>
            <p class="text-gray-500 text-sm">
                {{ $typeLabels[$consultation->type] ?? $consultation->type }} |
                Expediente: {{ $consultation->patient->medical_record_number }} |
                {{ $consultation->patient->age }} anios |
                {{ $consultation->consultation_date->format('d/m/Y H:i') }}
            </p>
        </div>
        <span style="background-color:#dbeafe;color:#1e40af;" class="px-3 py-1 text-sm font-semibold rounded-full">
            En progreso
        </span>
    </div>

    <form method="POST" action="{{ route('consultations.update', $consultation) }}">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Main SOAP form (3 cols) --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- S - SUBJECTIVE --}}
                <div class="bg-white rounded-lg shadow">
                    <div style="background-color:#dbeafe;" class="px-6 py-3 rounded-t-lg border-b">
                        <h3 class="font-semibold text-gray-800">S - Subjetivo</h3>
                        <p class="text-xs text-gray-500">Lo que el paciente refiere</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de consulta</label>
                            <textarea name="chief_complaint" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: Dolor al orinar desde hace 3 dias...">{{ old('chief_complaint', $consultation->chief_complaint) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Historia de la enfermedad actual</label>
                            <textarea name="history_present_illness" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Inicio, duracion, intensidad, factores agravantes/atenuantes...">{{ old('history_present_illness', $consultation->history_present_illness) }}</textarea>
                        </div>

                        {{-- Urinary symptoms (IPSS-based) --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Sintomas urinarios</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach([
                                    'frequency' => 'Frecuencia',
                                    'urgency' => 'Urgencia',
                                    'nocturia' => 'Nocturia',
                                    'weak_stream' => 'Chorro debil',
                                    'intermittency' => 'Intermitencia',
                                    'straining' => 'Esfuerzo',
                                    'incomplete_emptying' => 'Vaciado incompleto',
                                    'hematuria' => 'Hematuria',
                                    'dysuria' => 'Disuria',
                                    'incontinence' => 'Incontinencia',
                                ] as $key => $label)
                                    <label class="flex items-center text-sm">
                                        <input type="checkbox" name="urinary_symptoms[{{ $key }}]" value="1"
                                               {{ !empty($us[$key]) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 mr-2">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">IPSS Score (0-35)</label>
                                    <input type="number" name="urinary_symptoms[ipss_score]" min="0" max="35"
                                           value="{{ $us['ipss_score'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Calidad de vida (0-6)</label>
                                    <input type="number" name="urinary_symptoms[quality_of_life]" min="0" max="6"
                                           value="{{ $us['quality_of_life'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- Sexual function --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Funcion sexual</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach([
                                    'erectile_dysfunction' => 'Disfuncion erectil',
                                    'decreased_libido' => 'Libido disminuida',
                                    'premature_ejaculation' => 'Eyaculacion precoz',
                                    'painful_ejaculation' => 'Eyaculacion dolorosa',
                                    'hematospermia' => 'Hematospermia',
                                ] as $key => $label)
                                    <label class="flex items-center text-sm">
                                        <input type="checkbox" name="sexual_function[{{ $key }}]" value="1"
                                               {{ !empty($sf[$key]) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 mr-2">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <label class="block text-xs text-gray-500 mb-1">IIEF/SHIM Score</label>
                                <input type="number" name="sexual_function[iief_score]" min="0" max="25"
                                       value="{{ $sf['iief_score'] ?? '' }}"
                                       class="w-32 px-3 py-1 border border-gray-300 rounded-md text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Revision por sistemas</label>
                            <textarea name="review_of_systems" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('review_of_systems', $consultation->review_of_systems) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- O - OBJECTIVE --}}
                <div class="bg-white rounded-lg shadow">
                    <div style="background-color:#dcfce7;" class="px-6 py-3 rounded-t-lg border-b">
                        <h3 class="font-semibold text-gray-800">O - Objetivo</h3>
                        <p class="text-xs text-gray-500">Hallazgos del examen fisico</p>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- Vital signs --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Signos vitales</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">PA (mmHg)</label>
                                    <input type="text" name="vital_signs[blood_pressure]" placeholder="120/80"
                                           value="{{ $vs['blood_pressure'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">FC (lpm)</label>
                                    <input type="number" name="vital_signs[heart_rate]"
                                           value="{{ $vs['heart_rate'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Temp (C)</label>
                                    <input type="number" step="0.1" name="vital_signs[temperature]"
                                           value="{{ $vs['temperature'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Peso (kg)</label>
                                    <input type="number" step="0.1" name="vital_signs[weight]"
                                           value="{{ $vs['weight'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Talla (cm)</label>
                                    <input type="number" name="vital_signs[height]"
                                           value="{{ $vs['height'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">SpO2 (%)</label>
                                    <input type="number" name="vital_signs[oxygen_saturation]"
                                           value="{{ $vs['oxygen_saturation'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">FR (rpm)</label>
                                    <input type="number" name="vital_signs[respiratory_rate]"
                                           value="{{ $vs['respiratory_rate'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Examen fisico general</label>
                            <textarea name="physical_exam" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Aspecto general, estado de conciencia...">{{ old('physical_exam', $consultation->physical_exam) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Abdomen</label>
                            <textarea name="abdomen_exam" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Blando, depresible, sin masas, Punio percusion...">{{ old('abdomen_exam', $consultation->abdomen_exam) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Examen genitourinario</label>
                            <textarea name="genitourinary_exam" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Genitales externos, meato uretral, testiculos, pene, inguinal...">{{ old('genitourinary_exam', $consultation->genitourinary_exam) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tacto rectal</label>
                            <textarea name="rectal_exam" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Prostata: tamano, consistencia, nodulos, sensibilidad, surco medio...">{{ old('rectal_exam', $consultation->rectal_exam) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- A - ASSESSMENT --}}
                <div class="bg-white rounded-lg shadow">
                    <div style="background-color:#fef3c7;" class="px-6 py-3 rounded-t-lg border-b">
                        <h3 class="font-semibold text-gray-800">A - Evaluacion</h3>
                        <p class="text-xs text-gray-500">Diagnosticos e impresion clinica</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Impresion diagnostica</label>
                            <textarea name="assessment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Analisis e impresion clinica...">{{ old('assessment', $consultation->assessment) }}</textarea>
                        </div>

                        {{-- Diagnoses (ICD-10) --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Diagnosticos (CIE-10)</h4>
                            <div class="space-y-2" id="diagnoses-container">
                                @forelse($dx as $i => $d)
                                    <div class="flex gap-2 items-center diagnosis-row">
                                        <input type="text" name="diagnoses[{{ $i }}][code]" value="{{ $d['code'] ?? '' }}" placeholder="CIE-10"
                                               class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm">
                                        <input type="text" name="diagnoses[{{ $i }}][description]" value="{{ $d['description'] ?? '' }}" placeholder="Descripcion del diagnostico"
                                               class="flex-1 px-2 py-1 border border-gray-300 rounded-md text-sm">
                                        <select name="diagnoses[{{ $i }}][type]" class="w-32 px-2 py-1 border border-gray-300 rounded-md text-sm">
                                            <option value="primary" {{ ($d['type'] ?? '') === 'primary' ? 'selected' : '' }}>Principal</option>
                                            <option value="secondary" {{ ($d['type'] ?? '') === 'secondary' ? 'selected' : '' }}>Secundario</option>
                                        </select>
                                    </div>
                                @empty
                                    <div class="flex gap-2 items-center diagnosis-row">
                                        <input type="text" name="diagnoses[0][code]" placeholder="CIE-10"
                                               class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm">
                                        <input type="text" name="diagnoses[0][description]" placeholder="Descripcion del diagnostico"
                                               class="flex-1 px-2 py-1 border border-gray-300 rounded-md text-sm">
                                        <select name="diagnoses[0][type]" class="w-32 px-2 py-1 border border-gray-300 rounded-md text-sm">
                                            <option value="primary">Principal</option>
                                            <option value="secondary">Secundario</option>
                                        </select>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" onclick="addDiagnosis()" class="mt-2 text-sm text-blue-600 hover:underline">+ Agregar diagnostico</button>
                        </div>
                    </div>
                </div>

                {{-- P - PLAN --}}
                <div class="bg-white rounded-lg shadow">
                    <div style="background-color:#fce7f3;" class="px-6 py-3 rounded-t-lg border-b">
                        <h3 class="font-semibold text-gray-800">P - Plan</h3>
                        <p class="text-xs text-gray-500">Tratamiento y seguimiento</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Plan de tratamiento</label>
                            <textarea name="treatment_plan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Medicamentos, indicaciones, medidas generales...">{{ old('treatment_plan', $consultation->treatment_plan) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordenes diagnosticas</label>
                            <textarea name="diagnostic_orders" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Laboratorios, imagenes, estudios especiales...">{{ old('diagnostic_orders', $consultation->diagnostic_orders) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recomendacion quirurgica</label>
                            <textarea name="surgical_recommendation" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Procedimiento recomendado, si aplica...">{{ old('surgical_recommendation', $consultation->surgical_recommendation) }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Indicaciones de seguimiento</label>
                                <textarea name="follow_up_instructions" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('follow_up_instructions', $consultation->follow_up_instructions) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Proxima cita en (dias)</label>
                                <input type="number" name="follow_up_days" value="{{ old('follow_up_days', $consultation->follow_up_days) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Referencia / Interconsulta</label>
                            <textarea name="referrals" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('referrals', $consultation->referrals) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Private notes --}}
                <div class="bg-white rounded-lg shadow">
                    <div style="background-color:#f3f4f6;" class="px-6 py-3 rounded-t-lg border-b">
                        <h3 class="font-semibold text-gray-800">Notas privadas</h3>
                        <p class="text-xs text-gray-500">Solo visibles para el medico</p>
                    </div>
                    <div class="p-6">
                        <textarea name="private_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Notas personales, recordatorios...">{{ old('private_notes', $consultation->private_notes) }}</textarea>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex justify-between items-center">
                    <a href="{{ route('consultations.index') }}" class="text-gray-500 hover:underline text-sm">Cancelar</a>
                    <div class="flex gap-3">
                        <button type="submit" name="action" value="save" style="background-color:#2563eb;color:#fff;" class="px-6 py-2 rounded-md text-sm font-medium">
                            Guardar borrador
                        </button>
                        <button type="submit" name="action" value="sign" style="background-color:#16a34a;color:#fff;" class="px-6 py-2 rounded-md text-sm font-medium"
                                onclick="return confirm('Al firmar la consulta se cierra y no podra modificarse. Continuar?')">
                            Firmar y cerrar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Sidebar: patient info + history (1 col) --}}
            <div class="space-y-6">
                {{-- Patient card --}}
                <div class="bg-white rounded-lg shadow p-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Paciente</h4>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-gray-500">Nombre</dt><dd class="font-medium">{{ $consultation->patient->full_name }}</dd></div>
                        <div><dt class="text-gray-500">Edad</dt><dd>{{ $consultation->patient->age }} anios</dd></div>
                        <div><dt class="text-gray-500">Genero</dt><dd>{{ $consultation->patient->gender === 'male' ? 'Masculino' : ($consultation->patient->gender === 'female' ? 'Femenino' : 'Otro') }}</dd></div>
                        @if($consultation->patient->blood_type)
                            <div><dt class="text-gray-500">Sangre</dt><dd>{{ $consultation->patient->blood_type }}</dd></div>
                        @endif
                        @if($consultation->patient->phone)
                            <div><dt class="text-gray-500">Tel</dt><dd>{{ $consultation->patient->phone }}</dd></div>
                        @endif
                    </dl>
                    <a href="{{ route('patients.show', $consultation->patient) }}" class="block mt-3 text-xs text-blue-600 hover:underline">Ver ficha completa</a>
                </div>

                {{-- Medical history summary --}}
                @if($consultation->patient->medicalHistory)
                    @php $mh = $consultation->patient->medicalHistory; @endphp
                    <div class="bg-white rounded-lg shadow p-4">
                        <h4 class="font-semibold text-gray-800 mb-3">Antecedentes</h4>
                        <dl class="space-y-2 text-xs">
                            @if(!empty($mh->allergies))
                                <div><dt class="text-red-600 font-semibold">Alergias</dt><dd>{{ is_array($mh->allergies) ? implode(', ', $mh->allergies) : $mh->allergies }}</dd></div>
                            @endif
                            @if(!empty($mh->chronic_conditions))
                                <div><dt class="text-gray-500 font-semibold">Cronicos</dt><dd>{{ is_array($mh->chronic_conditions) ? implode(', ', $mh->chronic_conditions) : $mh->chronic_conditions }}</dd></div>
                            @endif
                            @if(!empty($mh->current_medications))
                                <div><dt class="text-gray-500 font-semibold">Medicamentos</dt><dd>{{ is_array($mh->current_medications) ? implode(', ', $mh->current_medications) : $mh->current_medications }}</dd></div>
                            @endif
                            @if(!empty($mh->surgical_history))
                                <div><dt class="text-gray-500 font-semibold">Cirugias previas</dt><dd>{{ is_array($mh->surgical_history) ? implode(', ', $mh->surgical_history) : $mh->surgical_history }}</dd></div>
                            @endif
                        </dl>
                    </div>
                @endif

                {{-- Previous consultations --}}
                @if($previousConsultations->isNotEmpty())
                    <div class="bg-white rounded-lg shadow p-4">
                        <h4 class="font-semibold text-gray-800 mb-3">Consultas previas</h4>
                        <div class="space-y-2">
                            @foreach($previousConsultations as $prev)
                                <a href="{{ route('consultations.show', $prev) }}" class="block p-2 rounded hover:bg-gray-50 text-xs">
                                    <div class="font-medium text-gray-800">{{ $prev->consultation_date->format('d/m/Y') }}</div>
                                    <div class="text-gray-500 truncate">{{ $prev->chief_complaint ?? 'Sin motivo registrado' }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <script>
    let diagCount = {{ max(count($dx), 1) }};
    function addDiagnosis() {
        const container = document.getElementById('diagnoses-container');
        const html = `<div class="flex gap-2 items-center diagnosis-row">
            <input type="text" name="diagnoses[${diagCount}][code]" placeholder="CIE-10" class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm">
            <input type="text" name="diagnoses[${diagCount}][description]" placeholder="Descripcion del diagnostico" class="flex-1 px-2 py-1 border border-gray-300 rounded-md text-sm">
            <select name="diagnoses[${diagCount}][type]" class="w-32 px-2 py-1 border border-gray-300 rounded-md text-sm">
                <option value="primary">Principal</option>
                <option value="secondary">Secundario</option>
            </select>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        diagCount++;
    }
    </script>
</x-layouts.tenant>
