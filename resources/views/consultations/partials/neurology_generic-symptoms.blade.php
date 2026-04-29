@php $sd = $consultation->specialty_data ?? []; @endphp

{{-- Neurological symptoms --}}
<div class="border border-gray-200 rounded-lg p-4">
    <h4 class="text-sm font-semibold text-gray-700 mb-3">Sintomas neurologicos</h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            'headache' => 'Cefalea',
            'seizures' => 'Convulsiones',
            'paresthesia' => 'Parestesias',
            'weakness' => 'Debilidad',
            'tremor' => 'Temblor',
            'dizziness' => 'Vertigo/Mareo',
            'speech_disorder' => 'Trastorno del habla',
            'visual_disorder' => 'Trastorno visual',
            'memory_loss' => 'Perdida de memoria',
            'gait_disorder' => 'Trastorno de la marcha',
            'numbness' => 'Entumecimiento',
            'syncope' => 'Sincope',
        ] as $key => $label)
            <label class="flex items-center text-sm">
                <input type="checkbox" name="specialty_data[neuro_symptoms][{{ $key }}]" value="1"
                       {{ !empty($sd['neuro_symptoms'][$key]) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 mr-2">
                {{ $label }}
            </label>
        @endforeach
    </div>
    <div class="mt-3 grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Glasgow (3-15)</label>
            <input type="number" name="specialty_data[glasgow_score]" min="3" max="15"
                   value="{{ $sd['glasgow_score'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">NIHSS (0-42)</label>
            <input type="number" name="specialty_data[nihss_score]" min="0" max="42"
                   value="{{ $sd['nihss_score'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
    </div>
</div>
