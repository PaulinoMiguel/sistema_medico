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
