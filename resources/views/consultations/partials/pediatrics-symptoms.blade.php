@php $sd = $consultation->specialty_data ?? []; @endphp

{{-- Growth data --}}
<div class="border border-gray-200 rounded-lg p-4">
    <h4 class="text-sm font-semibold text-gray-700 mb-3">Datos de crecimiento</h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Perimetro cefalico (cm)</label>
            <input type="number" step="0.1" name="specialty_data[head_circumference]"
                   value="{{ $sd['head_circumference'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Percentil peso</label>
            <input type="number" step="0.1" name="specialty_data[weight_percentile]"
                   value="{{ $sd['weight_percentile'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Percentil talla</label>
            <input type="number" step="0.1" name="specialty_data[height_percentile]"
                   value="{{ $sd['height_percentile'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Percentil PC</label>
            <input type="number" step="0.1" name="specialty_data[hc_percentile]"
                   value="{{ $sd['hc_percentile'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
    </div>
</div>

{{-- Psychomotor development --}}
<div class="border border-gray-200 rounded-lg p-4">
    <h4 class="text-sm font-semibold text-gray-700 mb-3">Desarrollo psicomotor</h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            'head_control' => 'Sostiene cabeza',
            'sits' => 'Se sienta',
            'crawls' => 'Gatea',
            'walks' => 'Camina',
            'speaks_words' => 'Dice palabras',
            'speaks_sentences' => 'Forma oraciones',
            'sphincter_control' => 'Control esfinteres',
            'social_smile' => 'Sonrisa social',
        ] as $key => $label)
            <label class="flex items-center text-sm">
                <input type="checkbox" name="specialty_data[development][{{ $key }}]" value="1"
                       {{ !empty($sd['development'][$key]) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 mr-2">
                {{ $label }}
            </label>
        @endforeach
    </div>
    <div class="mt-3">
        <label class="block text-xs text-gray-500 mb-1">Observaciones del desarrollo</label>
        <textarea name="specialty_data[development_notes]" rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ $sd['development_notes'] ?? '' }}</textarea>
    </div>
</div>

{{-- Feeding --}}
<div class="border border-gray-200 rounded-lg p-4">
    <h4 class="text-sm font-semibold text-gray-700 mb-3">Alimentacion</h4>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tipo de alimentacion</label>
            <select name="specialty_data[feeding_type]"
                    class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
                <option value="">Seleccionar...</option>
                @foreach(['breastfeeding' => 'Lactancia materna', 'formula' => 'Formula', 'mixed' => 'Mixta', 'complementary' => 'Complementaria', 'family_diet' => 'Dieta familiar'] as $k => $l)
                    <option value="{{ $k }}" {{ ($sd['feeding_type'] ?? '') === $k ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Inicio solidos (meses)</label>
            <input type="number" name="specialty_data[solids_start_months]"
                   value="{{ $sd['solids_start_months'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
    </div>
    <div class="mt-3">
        <label class="block text-xs text-gray-500 mb-1">Observaciones alimentacion</label>
        <textarea name="specialty_data[feeding_notes]" rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ $sd['feeding_notes'] ?? '' }}</textarea>
    </div>
</div>

{{-- Vaccination --}}
<div class="border border-gray-200 rounded-lg p-4">
    <h4 class="text-sm font-semibold text-gray-700 mb-3">Vacunacion</h4>
    <div class="flex items-center gap-4">
        <label class="flex items-center text-sm">
            <input type="checkbox" name="specialty_data[vaccines_up_to_date]" value="1"
                   {{ !empty($sd['vaccines_up_to_date']) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600 mr-2">
            Esquema de vacunacion al dia
        </label>
    </div>
    <div class="mt-3">
        <label class="block text-xs text-gray-500 mb-1">Observaciones vacunacion</label>
        <textarea name="specialty_data[vaccination_notes]" rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ $sd['vaccination_notes'] ?? '' }}</textarea>
    </div>
</div>
