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
        $prehistoryPartial = \App\Support\ConsultationTemplate::resolvePartial($templateSlug, 'pre-history');
        $symptomsPartial = \App\Support\ConsultationTemplate::resolvePartial($templateSlug, 'symptoms');
        $examsPartial = \App\Support\ConsultationTemplate::resolvePartial($templateSlug, 'exams');
    @endphp

    {{-- Header --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <a href="{{ route('consultations.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a consultas</a>
            <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $consultation->patient->full_name }}</h2>
            <p class="text-gray-500 text-sm">
                {{ $typeLabels[$consultation->type] ?? $consultation->type }} |
                Expediente: {{ $consultation->patient->medical_record_number }} |
                {{ $consultation->patient->age }} años |
                {{ $consultation->consultation_date->format('d/m/Y H:i') }}
            </p>
        </div>
        <span style="background-color:#dbeafe;color:#1e40af;" class="px-3 py-1 text-sm font-semibold rounded-full">
            En progreso
        </span>
    </div>

    @if($consultation->type !== 'initial')
        {{-- Consulta no-inicial: solo textarea de notas. La doctora prefiere
             un formato simple para controles, pre/post-quirurgicos, etc. --}}
        <form method="POST" action="{{ route('consultations.update', $consultation) }}">
            @csrf @method('PUT')
            <div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notas de la consulta</label>
                <textarea name="notes" rows="20" autofocus
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Escriba aqui los apuntes de la consulta...">{{ old('notes', $consultation->notes) }}</textarea>
                <div class="flex justify-between items-center mt-4">
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
        </form>
    @else
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

                        {{-- Template-specific pre-history (antecedentes capturados en consulta inicial) --}}
                        @includeIf($prehistoryPartial)

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Historia de la enfermedad actual</label>
                            <textarea name="history_present_illness" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Inicio, duracion, intensidad, factores agravantes/atenuantes...">{{ old('history_present_illness', $consultation->history_present_illness) }}</textarea>
                        </div>

                        {{-- Specialty-specific symptoms --}}
                        @includeIf($symptomsPartial)
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
                                    <label class="block text-xs text-gray-500 mb-1">FR (rpm)</label>
                                    <input type="number" name="vital_signs[respiratory_rate]"
                                           value="{{ $vs['respiratory_rate'] ?? '' }}"
                                           class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Abdomen</label>
                            <textarea name="abdomen_exam" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Blando, depresible, sin masas, Punio percusion...">{{ old('abdomen_exam', $consultation->abdomen_exam) }}</textarea>
                        </div>
                        {{-- Specialty-specific exams --}}
                        @includeIf($examsPartial)
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

                        {{-- Plantillas de ordenes para imprimir y entregar al paciente.
                             No persiste seleccion: es utilidad de impresion.
                             Por ahora solo urologia (las plantillas son urologicas). Cuando
                             el set se generalice o haya sets por especialidad, abrir este gate. --}}
                        @if ($specialtyKey === 'urology')
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700">Ordenes para imprimir</h4>
                                    <p class="text-xs text-gray-500">Marca las que vas a entregar al paciente. No se guardan en la consulta — solo se imprimen.</p>
                                </div>
                                <button type="button" id="print-orders-btn"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                    Imprimir ordenes
                                </button>
                            </div>
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-3">
                                <input type="checkbox" id="select-all-orders" class="rounded border-gray-300 text-blue-600 mr-2">
                                Marcar todas
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach(config('order_templates') as $catSlug => $cat)
                                    <div>
                                        <h5 class="text-xs font-semibold text-gray-600 uppercase mb-2">{{ $cat['label'] }}</h5>
                                        <div class="space-y-1">
                                            @foreach($cat['templates'] as $tplSlug => $tpl)
                                                <label class="flex items-start text-sm">
                                                    <input type="checkbox" class="order-template-check rounded border-gray-300 text-blue-600 mr-2 mt-0.5"
                                                           value="{{ $tplSlug }}">
                                                    <span>{{ $tpl['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recomendacion quirurgica</label>
                            <textarea name="surgical_recommendation" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Procedimiento recomendado, si aplica...">{{ old('surgical_recommendation', $consultation->surgical_recommendation) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proxima cita en (dias)</label>
                            <input type="number" name="follow_up_days" value="{{ old('follow_up_days', $consultation->follow_up_days) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
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
                        @can('prescriptions.create')
                        <a href="{{ route('prescriptions.create', ['patient_id' => $consultation->patient_id, 'consultation_id' => $consultation->id]) }}"
                           style="background-color:#9333ea;color:#fff;" class="px-6 py-2 rounded-md text-sm font-medium inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Crear receta
                        </a>
                        @endcan
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
                        <div><dt class="text-gray-500">Edad</dt><dd>{{ $consultation->patient->age }} años</dd></div>
                        <div><dt class="text-gray-500">Genero</dt><dd>{{ $consultation->patient->gender === 'male' ? 'Masculino' : ($consultation->patient->gender === 'female' ? 'Femenino' : 'Otro') }}</dd></div>
                        @if($consultation->patient->blood_type)
                            <div><dt class="text-gray-500">Sangre</dt><dd>{{ $consultation->patient->blood_type }}</dd></div>
                        @endif
                        @if($consultation->patient->document_number)
                            <div><dt class="text-gray-500">Cedula</dt><dd>{{ $consultation->patient->document_number }}</dd></div>
                        @endif
                    </dl>
                    <a href="{{ route('patients.show', ['patient' => $consultation->patient, 'from' => 'consultation', 'consultation_id' => $consultation->id]) }}" class="block mt-3 text-xs text-blue-600 hover:underline">Ver ficha completa</a>
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

    @if ($specialtyKey === 'urology')
    <script>
        const orderChecks = document.querySelectorAll('.order-template-check');
        const selectAll = document.getElementById('select-all-orders');

        selectAll.addEventListener('change', () => {
            orderChecks.forEach(cb => { cb.checked = selectAll.checked; });
        });

        orderChecks.forEach(cb => {
            cb.addEventListener('change', () => {
                const total = orderChecks.length;
                const checked = Array.from(orderChecks).filter(c => c.checked).length;
                selectAll.checked = checked === total;
                selectAll.indeterminate = checked > 0 && checked < total;
            });
        });

        document.getElementById('print-orders-btn').addEventListener('click', () => {
            const checked = Array.from(orderChecks).filter(c => c.checked).map(cb => encodeURIComponent(cb.value));
            if (checked.length === 0) {
                alert('Marca al menos una orden para imprimir.');
                return;
            }
            const params = checked.map(v => 'items[]=' + v).join('&');
            const url = '{{ route('consultations.print-orders', $consultation) }}?' + params;
            window.open(url, '_blank');
        });
    </script>
    @endif
    @endif

</x-layouts.tenant>
