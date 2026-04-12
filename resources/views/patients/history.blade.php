<x-layouts.tenant :title="'Historial - ' . $patient->full_name">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $patient->full_name }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Expediente: {{ $patient->medical_record_number }} |
            {{ $patient->age }} años |
            {{ $patient->gender == 'male' ? 'Masculino' : ($patient->gender == 'female' ? 'Femenino' : 'Otro') }}
        </p>
    </div>

    @php
        $mh = $patient->medicalHistory;
        $hasData = $mh && (
            !empty($mh->allergies) || !empty($mh->chronic_conditions) || !empty($mh->current_medications) ||
            !empty($mh->surgical_history) || !empty($mh->family_history) || !empty($mh->habits) ||
            !empty($mh->urological_history) || !empty($mh->obstetric_gynecological) || !empty($mh->immunizations)
        );
    @endphp

    @if(!$hasData)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-8 text-center">
            <svg class="w-12 h-12 text-yellow-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-yellow-800 dark:text-yellow-300 font-medium text-lg">Este paciente no cuenta con historial clinico</p>
            <p class="text-yellow-600 dark:text-yellow-400 text-sm mt-1">El historial se completa durante las consultas medicas.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if(!empty($mh->allergies))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Alergias</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->allergies as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($mh->chronic_conditions))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Condiciones cronicas</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->chronic_conditions as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($mh->current_medications))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Medicamentos actuales</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->current_medications as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($mh->surgical_history))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Historial quirurgico</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->surgical_history as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($mh->family_history))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Historial familiar</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->family_history as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($mh->habits))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Habitos</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->habits as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($mh->urological_history))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Historial urologico</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->urological_history as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($mh->obstetric_gynecological))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Gineco-obstetrico</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->obstetric_gynecological as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($mh->immunizations))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Inmunizaciones</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach((array)$mh->immunizations as $item)
                        <li>{{ is_array($item) ? ($item['name'] ?? json_encode($item)) : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    @endif
</x-layouts.tenant>
