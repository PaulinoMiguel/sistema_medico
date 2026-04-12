<x-layouts.tenant title="Nueva Receta">
    <div class="mb-6">
        @if(isset($consultationId) && $consultationId)
            <a href="{{ route('consultations.edit', $consultationId) }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a la consulta</a>
        @else
            <a href="{{ route('prescriptions.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a recetas</a>
        @endif
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Nueva Receta</h2>

    <form action="{{ route('prescriptions.store') }}" method="POST" id="prescriptionForm">
        @csrf
        <input type="hidden" name="consultation_id" value="{{ $consultationId ?? '' }}">

        {{-- Patient & Diagnosis --}}
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-3 border-b bg-gray-50 rounded-t-lg">
                <h3 class="font-semibold text-gray-800">Datos Generales</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">Paciente *</label>
                        <a href="#" id="view-patient-history" target="_blank"
                           class="text-xs text-blue-600 hover:underline hidden">Ver historial del paciente</a>
                    </div>
                    <select name="patient_id" id="patient_select" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Seleccionar paciente...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ (string)($selectedPatientId ?? '') === (string)$patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }} - {{ $patient->medical_record_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas generales</label>
                    <textarea name="notes" rows="2"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                              placeholder="Notas adicionales para la receta...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Medications --}}
        <div class="bg-white rounded-lg shadow mb-6">
            <div style="background-color:#dbeafe;" class="px-6 py-3 rounded-t-lg border-b flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Medicamentos</h3>
                <button type="button" onclick="addMedication()"
                        class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">+ Agregar</button>
            </div>
            <div id="medications-container" class="divide-y divide-gray-200">
                {{-- First medication row added by JS --}}
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('prescriptions.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-200">Cancelar</a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Guardar Receta</button>
        </div>
    </form>

    @php
        $routeOptions = [
            'oral' => 'Oral',
            'sublingual' => 'Sublingual',
            'topical' => 'Topica',
            'intramuscular' => 'Intramuscular',
            'intravenous' => 'Intravenosa',
            'rectal' => 'Rectal',
            'ophthalmic' => 'Oftalmica',
            'otic' => 'Otica',
            'nasal' => 'Nasal',
            'inhaled' => 'Inhalada',
        ];
    @endphp

    <template id="medication-template">
        <div class="p-6 medication-row">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-medium text-gray-700">Medicamento <span class="med-number"></span></h4>
                <button type="button" onclick="removeMedication(this)" class="text-red-500 hover:text-red-700 text-sm">Eliminar</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del medicamento *</label>
                    <input type="text" data-name="medication_name" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="Ej: Tamsulosina">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dosis *</label>
                    <input type="text" data-name="dosage" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="Ej: 0.4mg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frecuencia *</label>
                    <input type="text" data-name="frequency" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="Ej: Cada 24 horas">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duracion</label>
                    <input type="text" data-name="duration"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="Ej: 30 dias">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Via de administracion *</label>
                    <select data-name="route" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @foreach($routeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                    <input type="number" data-name="quantity" min="1"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="Ej: 30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Indicaciones adicionales</label>
                    <input type="text" data-name="instructions"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="Ej: Tomar despues de cenar">
                </div>
            </div>
        </div>
    </template>

    <script>
        // Patient history link
        (function () {
            const select = document.getElementById('patient_select');
            const link = document.getElementById('view-patient-history');
            const baseUrl = '{{ url("patients") }}/';

            function updateLink() {
                if (select.value) {
                    link.href = baseUrl + select.value;
                    link.classList.remove('hidden');
                } else {
                    link.classList.add('hidden');
                }
            }

            select.addEventListener('change', updateLink);
            updateLink();
        })();

        let medicationCount = 0;

        function addMedication() {
            const container = document.getElementById('medications-container');
            const template = document.getElementById('medication-template');
            const clone = template.content.cloneNode(true);
            const index = medicationCount;

            clone.querySelector('.med-number').textContent = index + 1;

            // Set proper name attributes
            clone.querySelectorAll('[data-name]').forEach(el => {
                el.setAttribute('name', `items[${index}][${el.dataset.name}]`);
            });

            container.appendChild(clone);
            medicationCount++;
            updateNumbers();
        }

        function removeMedication(btn) {
            const row = btn.closest('.medication-row');
            if (document.querySelectorAll('.medication-row').length > 1) {
                row.remove();
                reindexMedications();
                updateNumbers();
            }
        }

        function reindexMedications() {
            document.querySelectorAll('.medication-row').forEach((row, index) => {
                row.querySelectorAll('[data-name]').forEach(el => {
                    el.setAttribute('name', `items[${index}][${el.dataset.name}]`);
                });
            });
            medicationCount = document.querySelectorAll('.medication-row').length;
        }

        function updateNumbers() {
            document.querySelectorAll('.medication-row').forEach((row, index) => {
                row.querySelector('.med-number').textContent = index + 1;
            });
        }

        // Start with one medication row
        addMedication();
    </script>
</x-layouts.tenant>
