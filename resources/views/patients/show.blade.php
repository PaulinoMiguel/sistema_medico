<x-layouts.tenant :title="$patient->full_name">
    @include('patients.partials.header-tabs')

    {{-- ===== Pestaña: Resumen ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Contacto --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Información de contacto</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="text-gray-800 dark:text-gray-200">{{ $patient->email ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Teléfono</dt>
                    <dd class="text-gray-800 dark:text-gray-200">{{ $patient->phone ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Dirección</dt>
                    <dd class="text-gray-800 dark:text-gray-200">{{ $patient->address ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Aseguradora</dt>
                    <dd class="text-gray-800 dark:text-gray-200">{{ $patient->insurance_provider ?? 'Particular' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Emergencia</dt>
                    <dd class="text-gray-800 dark:text-gray-200">{{ $patient->emergency_contact_name ?? '-' }} {{ $patient->emergency_contact_phone ? '('.$patient->emergency_contact_phone.')' : '' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Últimos turnos --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Últimos turnos</h3>
            @if($patient->appointments->isEmpty())
                <p class="text-gray-500 dark:text-gray-400 text-sm">Sin turnos registrados.</p>
            @else
                <div class="space-y-3">
                    @foreach($patient->appointments as $apt)
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $apt->scheduled_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $apt->doctor->name ?? 'Doctor' }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full
                            {{ $apt->status == 'completed' ? 'bg-green-100 text-green-800' : ($apt->status == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ $apt->status }}
                        </span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.tenant>
