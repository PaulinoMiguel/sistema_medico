<x-layouts.tenant :title="'Editar Receta - ' . $prescription->prescription_number">
    <div class="mb-6">
        <a href="{{ route('prescriptions.show', $prescription) }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a receta</a>
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Receta {{ $prescription->prescription_number }}</h2>

    <form action="{{ route('prescriptions.update', $prescription) }}" method="POST" id="prescriptionForm">
        @csrf
        @method('PUT')

        {{-- Patient & Diagnosis --}}
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-3 border-b bg-gray-50 rounded-t-lg">
                <h3 class="font-semibold text-gray-800">Datos Generales</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paciente</label>
                    <p class="text-sm text-gray-800 py-2">{{ $prescription->patient->full_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="active" {{ $prescription->status === 'active' ? 'selected' : '' }}>Activa</option>
                        <option value="expired" {{ $prescription->status === 'expired' ? 'selected' : '' }}>Vencida</option>
                        <option value="cancelled" {{ $prescription->status === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas generales</label>
                    <textarea name="notes" rows="2"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                              placeholder="Notas adicionales...">{{ old('notes', $prescription->notes) }}</textarea>
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
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('prescriptions.show', $prescription) }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-200">Cancelar</a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Actualizar Receta</button>
        </div>
    </form>

    @php
        $routeOptions = [
            'oral' => 'Oral',
            'sublingual' => 'Sublingual',
            'topical' => 'Tópica',
            'intramuscular' => 'Intramuscular',
            'intravenous' => 'Intravenosa',
            'rectal' => 'Rectal',
            'ophthalmic' => 'Oftálmica',
            'otic' => 'Ótica',
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
            @if($medications->isNotEmpty())
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Elegir del banco</label>
                <select class="med-bank-select w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm bg-blue-50">
                    <option value="">— Elegir un medicamento guardado —</option>
                    @foreach($medications as $m)
                        <option value="{{ $m->id }}"
                                data-name="{{ $m->name }}"
                                data-dosage="{{ $m->dosage }}"
                                data-duration="{{ $m->duration }}"
                                data-route="{{ $m->route }}"
                                data-instructions="{{ $m->instructions }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medicamento *</label>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duración</label>
                    <input type="text" data-name="duration"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="Ej: 30 días">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vía *</label>
                    <select data-name="route" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        @foreach($routeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
                    <input type="text" data-name="instructions"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="Ej: Tomar después de cenar">
                </div>
            </div>
        </div>
    </template>

    <script>
        let medicationCount = 0;
        const existingItems = @json($prescription->items);

        // Rellenar la fila desde el banco de medicamentos
        document.getElementById('medications-container').addEventListener('change', function (e) {
            if (!e.target.classList.contains('med-bank-select')) return;
            const opt = e.target.selectedOptions[0];
            if (!opt || !opt.value) return;
            const row = e.target.closest('.medication-row');
            // Solo se rellena el nombre; la doctora completa dosis/duración/vía/observación.
            const nameEl = row.querySelector('[data-name="medication_name"]');
            if (nameEl) nameEl.value = opt.dataset.name ?? '';
        });

        function addMedication(data = null) {
            const container = document.getElementById('medications-container');
            const template = document.getElementById('medication-template');
            const clone = template.content.cloneNode(true);
            const index = medicationCount;

            clone.querySelector('.med-number').textContent = index + 1;

            clone.querySelectorAll('[data-name]').forEach(el => {
                const fieldName = el.dataset.name;
                el.setAttribute('name', `items[${index}][${fieldName}]`);
                if (data && data[fieldName] !== null && data[fieldName] !== undefined) {
                    el.value = data[fieldName];
                }
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

        // Load existing items
        if (existingItems.length > 0) {
            existingItems.forEach(item => addMedication(item));
        } else {
            addMedication();
        }
    </script>
</x-layouts.tenant>
