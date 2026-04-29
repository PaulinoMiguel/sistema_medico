@php
    $mh = $consultation->patient->medicalHistory;
    $mhFlat = fn ($v) => is_array($v) ? implode(', ', $v) : ($v ?? '');
@endphp

<div class="border border-gray-200 rounded-lg p-4 space-y-3">
    <div>
        <h4 class="text-sm font-semibold text-gray-700">Antecedentes</h4>
        <p class="text-xs text-gray-500">Datos a nivel paciente. Quedan disponibles en la barra lateral en consultas siguientes.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Alergias</label>
        <textarea name="medical_history[allergies]" rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('medical_history.allergies', $mhFlat($mh?->allergies)) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Antecedentes personales</label>
        <textarea name="medical_history[chronic_conditions]" rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('medical_history.chronic_conditions', $mhFlat($mh?->chronic_conditions)) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Cirugias</label>
        <textarea name="medical_history[surgical_history]" rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('medical_history.surgical_history', $mhFlat($mh?->surgical_history)) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Medicamentos</label>
        <textarea name="medical_history[current_medications]" rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('medical_history.current_medications', $mhFlat($mh?->current_medications)) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Habitos evacuatorios</label>
        <textarea name="medical_history[habits]" rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('medical_history.habits', $mhFlat($mh?->habits)) }}</textarea>
    </div>
</div>
