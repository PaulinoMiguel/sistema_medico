<x-layouts.tenant :title="'Nueva Consulta'">
    @php
        $fixedPatient = $selectedPatient ?? null;
        $apptId = $appointmentId ?? null;
        $type = $defaultType ?? null;
        $backUrl = $apptId
            ? route('appointments.show', $apptId)
            : ($fixedPatient ? route('patients.consultations', $fixedPatient) : route('patients.index'));
    @endphp

    <div class="mb-6">
        <a href="{{ $backUrl }}" class="text-blue-600 hover:underline text-sm">&larr; Volver</a>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-2">Nueva Consulta (sin turno)</h2>
    </div>

    <form method="POST" action="{{ route('consultations.store') }}">
        @csrf
        @if($apptId)
            <input type="hidden" name="appointment_id" value="{{ $apptId }}">
        @endif
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-lg">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paciente *</label>
                    @if($fixedPatient)
                        {{-- Paciente fijo: contexto del expediente, no se puede cambiar --}}
                        <input type="hidden" name="patient_id" value="{{ $fixedPatient->id }}">
                        <div class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 rounded-md text-sm text-gray-800 dark:text-gray-200">
                            {{ $fixedPatient->full_name }} ({{ $fixedPatient->medical_record_number }})
                        </div>
                    @else
                        <select name="patient_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccionar...</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ (string) $selectedPatientId === (string) $p->id ? 'selected' : '' }}>{{ $p->full_name }} ({{ $p->medical_record_number }})</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de consulta *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="initial" @selected($type === 'initial')>Consulta inicial</option>
                        <option value="follow_up" @selected($type === 'follow_up')>Control</option>
                        <option value="pre_operative" @selected($type === 'pre_operative')>Pre-quirúrgico</option>
                        <option value="post_operative" @selected($type === 'post_operative')>Post-quirúrgico</option>
                        <option value="urodynamic" @selected($type === 'urodynamic')>Urodinamia</option>
                        <option value="flowmetry" @selected($type === 'flowmetry')>Flujometría</option>
                        <option value="procedure" @selected($type === 'procedure')>Procedimiento</option>
                        <option value="emergency" @selected($type === 'emergency')>Urgencia</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <a href="{{ $backUrl }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</a>
                <button type="submit" style="background-color:#2563eb;color:#fff;" class="px-6 py-2 rounded-md text-sm font-medium">Iniciar consulta</button>
            </div>
        </div>
    </form>
</x-layouts.tenant>
