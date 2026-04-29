@php $sd = $consultation->specialty_data ?? []; @endphp

{{-- Growth / antropometria --}}
<div class="border border-gray-200 rounded-lg p-4" id="anthro_block"
     data-calc-url="{{ route('patients.measurements.calculate', $consultation->patient) }}">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-700">Antropometria</h4>
        <span class="text-xs text-gray-500">Al guardar la consulta se registra una medicion.</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Peso (kg)</label>
            <input type="number" step="0.001" min="0.3" max="250"
                   name="specialty_data[weight_kg]" id="anthro_weight"
                   value="{{ $sd['weight_kg'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Talla / longitud (cm)</label>
            <input type="number" step="0.1" min="30" max="220"
                   name="specialty_data[height_cm]" id="anthro_height"
                   value="{{ $sd['height_cm'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Perimetro cefalico (cm)</label>
            <input type="number" step="0.1" min="25" max="65"
                   name="specialty_data[head_circumference_cm]" id="anthro_hc"
                   value="{{ $sd['head_circumference_cm'] ?? '' }}"
                   class="w-full px-3 py-1 border border-gray-300 rounded-md text-sm">
        </div>
    </div>

    {{-- Live preview --}}
    <div id="anthro_preview" class="mt-3 hidden grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
        <div data-key="weight" class="bg-gray-50 rounded p-2">
            <p class="text-gray-500">Peso</p><p class="font-mono text-sm"><span class="z">-</span> &middot; P<span class="p">-</span></p>
        </div>
        <div data-key="height" class="bg-gray-50 rounded p-2">
            <p class="text-gray-500">Talla</p><p class="font-mono text-sm"><span class="z">-</span> &middot; P<span class="p">-</span></p>
        </div>
        <div data-key="head_circumference" class="bg-gray-50 rounded p-2">
            <p class="text-gray-500">PC</p><p class="font-mono text-sm"><span class="z">-</span> &middot; P<span class="p">-</span></p>
        </div>
        <div data-key="bmi" class="bg-gray-50 rounded p-2">
            <p class="text-gray-500">IMC</p><p class="font-mono text-sm"><span class="z">-</span> &middot; P<span class="p">-</span></p>
        </div>
    </div>

    <script>
        (function () {
            const block = document.getElementById('anthro_block');
            if (!block) return;
            const url = block.dataset.calcUrl;
            const preview = document.getElementById('anthro_preview');
            const inputs = ['anthro_weight', 'anthro_height', 'anthro_hc'].map(id => document.getElementById(id));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            let t = null;

            function recalc() {
                const [w, h, hc] = inputs.map(i => i.value);
                if (!w && !h && !hc) { preview.classList.add('hidden'); return; }
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        measured_at: new Date().toISOString().slice(0, 10),
                        weight_kg: w || null, height_cm: h || null, head_circumference_cm: hc || null,
                    }),
                }).then(r => r.ok ? r.json() : null).then(data => {
                    if (!data) return;
                    preview.classList.remove('hidden');
                    Object.entries(data.indicators).forEach(([key, v]) => {
                        const node = preview.querySelector(`[data-key="${key}"]`);
                        if (!node) return;
                        node.querySelector('.z').textContent = v ? `Z=${v.z}` : '-';
                        node.querySelector('.p').textContent = v ? v.percentile : '-';
                    });
                });
            }
            inputs.forEach(i => i.addEventListener('input', () => {
                clearTimeout(t); t = setTimeout(recalc, 350);
            }));
            recalc();
        })();
    </script>
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
