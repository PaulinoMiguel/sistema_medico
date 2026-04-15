<x-layouts.tenant :title="'Curvas de crecimiento'">
    <div class="mb-6">
        <a href="{{ route('patients.show', $patient) }}" class="text-blue-600 hover:underline text-sm">&larr; Volver al paciente</a>
        <div class="flex justify-between items-start mt-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Curvas de crecimiento</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ $patient->full_name }} &middot;
                    {{ $sex === 'male' ? 'Varon' : 'Mujer' }} &middot;
                    Nac: {{ $patient->date_of_birth->format('d/m/Y') }}
                    @if($isPreterm)
                        <span class="ml-2 px-2 py-0.5 rounded text-xs bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200">
                            Prematuro {{ $patient->gestational_age_weeks }} sem &mdash; usa edad corregida
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Form rapido nueva medicion --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded p-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 mb-6">
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">Registrar medicion</h3>
        <form method="POST" action="{{ route('patients.measurements.store', $patient) }}" id="measureForm">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Fecha *</label>
                    <input type="date" name="measured_at" id="m_date" required value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                           class="w-full px-2 py-1 border rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Peso (kg)</label>
                    <input type="number" name="weight_kg" id="m_weight" step="0.001" min="0.3" max="250"
                           class="w-full px-2 py-1 border rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Talla (cm)</label>
                    <input type="number" name="height_cm" id="m_height" step="0.1" min="30" max="220"
                           class="w-full px-2 py-1 border rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">P. Cefalico (cm)</label>
                    <input type="number" name="head_circumference_cm" id="m_hc" step="0.1" min="25" max="65"
                           class="w-full px-2 py-1 border rounded text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-3 py-1.5 rounded text-sm font-medium hover:bg-blue-700">
                        Guardar
                    </button>
                </div>
            </div>

            {{-- Live percentile preview --}}
            <div id="preview" class="mt-3 hidden grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div data-key="weight" class="bg-gray-50 dark:bg-gray-900 rounded p-2">
                    <p class="text-gray-500">Peso</p>
                    <p class="font-mono text-base"><span class="z">-</span> · P<span class="p">-</span></p>
                </div>
                <div data-key="height" class="bg-gray-50 dark:bg-gray-900 rounded p-2">
                    <p class="text-gray-500">Talla</p>
                    <p class="font-mono text-base"><span class="z">-</span> · P<span class="p">-</span></p>
                </div>
                <div data-key="head_circumference" class="bg-gray-50 dark:bg-gray-900 rounded p-2">
                    <p class="text-gray-500">P. Cefalico</p>
                    <p class="font-mono text-base"><span class="z">-</span> · P<span class="p">-</span></p>
                </div>
                <div data-key="bmi" class="bg-gray-50 dark:bg-gray-900 rounded p-2">
                    <p class="text-gray-500">IMC</p>
                    <p class="font-mono text-base"><span class="z">-</span> · P<span class="p">-</span></p>
                </div>
            </div>
        </form>
    </div>

    {{-- Charts grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">Peso para la edad</h3>
            <canvas id="chart_weight" height="280"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">Talla / longitud para la edad</h3>
            <canvas id="chart_height" height="280"></canvas>
        </div>
        @if($curves['head_circumference_for_age'])
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">Perimetro cefalico (0-36 meses)</h3>
            <canvas id="chart_hc" height="280"></canvas>
        </div>
        @endif
        @if($curves['bmi_for_age'])
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">IMC para la edad (2-18 anos)</h3>
            <canvas id="chart_bmi" height="280"></canvas>
        </div>
        @endif
    </div>

    {{-- Tabla historico --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mt-6">
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">Historico de mediciones</h3>
        @if($measurements->isEmpty())
            <p class="text-sm text-gray-500">Sin mediciones registradas todavia.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 uppercase border-b dark:border-gray-700">
                        <tr>
                            <th class="text-left py-2">Fecha</th>
                            <th class="text-right py-2">Edad (m)</th>
                            <th class="text-right py-2">Peso</th>
                            <th class="text-right py-2">Talla</th>
                            <th class="text-right py-2">PC</th>
                            <th class="text-right py-2">IMC</th>
                            <th class="text-left py-2 pl-3">Z (P/T/PC/IMC)</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($measurements->reverse() as $m)
                        <tr>
                            <td class="py-2">{{ $m->measured_at->format('d/m/Y') }}</td>
                            <td class="py-2 text-right font-mono">
                                {{ number_format($m->age_months, 1) }}
                                @if($m->corrected_age_months !== null)
                                    <br><span class="text-[10px] text-orange-600 dark:text-orange-400">corr: {{ number_format($m->corrected_age_months, 1) }}</span>
                                @endif
                            </td>
                            <td class="py-2 text-right font-mono">{{ $m->weight_kg ? number_format($m->weight_kg, 2) : '-' }}</td>
                            <td class="py-2 text-right font-mono">{{ $m->height_cm ? number_format($m->height_cm, 1) : '-' }}</td>
                            <td class="py-2 text-right font-mono">{{ $m->head_circumference_cm ? number_format($m->head_circumference_cm, 1) : '-' }}</td>
                            <td class="py-2 text-right font-mono">{{ $m->bmi ? number_format($m->bmi, 1) : '-' }}</td>
                            <td class="py-2 pl-3 font-mono text-xs text-gray-600 dark:text-gray-400">
                                {{ $m->weight_z ?? '-' }} / {{ $m->height_z ?? '-' }} / {{ $m->head_circumference_z ?? '-' }} / {{ $m->bmi_z ?? '-' }}
                            </td>
                            <td class="py-2 text-right">
                                <form method="POST" action="{{ route('patients.measurements.destroy', [$patient, $m]) }}" class="inline"
                                      onsubmit="return confirm('Eliminar esta medicion?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 text-xs hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @php
        $measurementsJs = $measurements->map(function ($m) {
            return [
                'date' => $m->measured_at->toDateString(),
                'age_months' => (float) $m->age_months,
                'corrected_age_months' => $m->corrected_age_months !== null ? (float) $m->corrected_age_months : null,
                'weight_kg' => $m->weight_kg !== null ? (float) $m->weight_kg : null,
                'height_cm' => $m->height_cm !== null ? (float) $m->height_cm : null,
                'head_circumference_cm' => $m->head_circumference_cm !== null ? (float) $m->head_circumference_cm : null,
                'bmi' => $m->bmi !== null ? (float) $m->bmi : null,
            ];
        });
    @endphp

    {{-- Chart.js + builder --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const curves = @json($curves);
            const measurements = @json($measurementsJs);
            const isPreterm = @json($isPreterm);

            const ageOf = m => m.corrected_age_months ?? m.age_months;

            function makeDataset(label, x, y, color, dashed = true) {
                return {
                    label, data: x.map((v, i) => ({ x: v, y: y[i] })),
                    borderColor: color, backgroundColor: color, borderWidth: 1.2,
                    borderDash: dashed ? [4, 3] : [], pointRadius: 0, tension: 0.3,
                };
            }

            function patientPoints(measurementKey, color) {
                return {
                    label: 'Paciente',
                    data: measurements
                        .filter(m => m[measurementKey] !== null)
                        .map(m => ({ x: ageOf(m), y: m[measurementKey] })),
                    borderColor: color, backgroundColor: color,
                    showLine: true, borderWidth: 2,
                    pointRadius: 4, pointHoverRadius: 6, tension: 0,
                };
            }

            function build(canvasId, indicatorKey, measurementKey, yLabel) {
                const c = curves[indicatorKey];
                if (!c || !document.getElementById(canvasId)) return;

                const datasets = [
                    makeDataset('P3',  c.x, c.p3,  '#9ca3af'),
                    makeDataset('P15', c.x, c.p15, '#9ca3af'),
                    makeDataset('P50', c.x, c.p50, '#16a34a', false),
                    makeDataset('P85', c.x, c.p15.map((_, i) => c.p85[i]), '#9ca3af'),
                    makeDataset('P97', c.x, c.p97, '#9ca3af'),
                    patientPoints(measurementKey, '#dc2626'),
                ];

                new Chart(document.getElementById(canvasId), {
                    type: 'line',
                    data: { datasets },
                    options: {
                        responsive: true,
                        animation: false,
                        scales: {
                            x: { type: 'linear', title: { display: true, text: indicatorKey === 'bmi_for_age' ? 'Edad (meses)' : 'Edad (meses)' }, ticks: { stepSize: 12 } },
                            y: { title: { display: true, text: yLabel } },
                        },
                        plugins: {
                            legend: { labels: { boxWidth: 12, font: { size: 10 } } },
                            tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}` } },
                        },
                    },
                });
            }

            build('chart_weight', 'weight_for_age', 'weight_kg', 'Peso (kg)');
            build('chart_height', 'height_for_age', 'height_cm', 'Talla (cm)');
            build('chart_hc',     'head_circumference_for_age', 'head_circumference_cm', 'PC (cm)');
            build('chart_bmi',    'bmi_for_age', 'bmi', 'IMC (kg/m2)');

            // ───── Live percentile preview as user types ─────
            const form = document.getElementById('measureForm');
            const preview = document.getElementById('preview');
            const fields = ['m_date', 'm_weight', 'm_height', 'm_hc'];
            const calcUrl = '{{ route("patients.measurements.calculate", $patient) }}';
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            let timeout = null;
            function recalc() {
                const date = document.getElementById('m_date').value;
                const w = document.getElementById('m_weight').value;
                const h = document.getElementById('m_height').value;
                const hc = document.getElementById('m_hc').value;
                if (!date || (!w && !h && !hc)) {
                    preview.classList.add('hidden');
                    return;
                }
                fetch(calcUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ measured_at: date, weight_kg: w || null, height_cm: h || null, head_circumference_cm: hc || null }),
                }).then(r => r.json()).then(data => {
                    preview.classList.remove('hidden');
                    Object.entries(data.indicators).forEach(([key, v]) => {
                        const node = preview.querySelector(`[data-key="${key}"]`);
                        if (!node) return;
                        node.querySelector('.z').textContent = v ? `Z=${v.z}` : '-';
                        node.querySelector('.p').textContent = v ? v.percentile : '-';
                    });
                });
            }
            fields.forEach(id => {
                document.getElementById(id).addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(recalc, 350);
                });
            });
        })();
    </script>
</x-layouts.tenant>
