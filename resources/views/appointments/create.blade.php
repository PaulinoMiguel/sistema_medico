<x-layouts.tenant :title="'Nuevo Turno'">
    <div class="mb-6">
        <a href="{{ route('appointments.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a turnos</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Nuevo Turno</h2>
    </div>

    <form method="POST" action="{{ route('appointments.store') }}">
        @csrf

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paciente *</label>
                    <select name="patient_id" id="patient_select" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar paciente...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}"
                                    data-doctor-id="{{ $patient->primary_doctor_id }}"
                                    {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }} ({{ $patient->medical_record_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($showDoctorSelect)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Doctor *</label>
                        <select name="doctor_id" id="doctor_select" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccionar doctor...</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->name }}@if($doctor->specialty) — {{ $doctor->specialty }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y hora *</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duracion (minutos)</label>
                    <select name="duration_minutes" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="15">15 min</option>
                        <option value="30" selected>30 min</option>
                        <option value="45">45 min</option>
                        <option value="60">1 hora</option>
                        <option value="90">1.5 horas</option>
                        <option value="120">2 horas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de turno *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="first_visit" {{ old('type') == 'first_visit' ? 'selected' : '' }}>Primera vez</option>
                        <option value="follow_up" {{ old('type') == 'follow_up' ? 'selected' : '' }}>Control</option>
                        <option value="pre_operative" {{ old('type') == 'pre_operative' ? 'selected' : '' }}>Pre-quirurgico</option>
                        <option value="post_operative" {{ old('type') == 'post_operative' ? 'selected' : '' }}>Post-quirurgico</option>
                        <option value="urodynamic_study" {{ old('type') == 'urodynamic_study' ? 'selected' : '' }}>Estudio urodinamico</option>
                        <option value="procedure" {{ old('type') == 'procedure' ? 'selected' : '' }}>Procedimiento</option>
                        <option value="surgical" {{ old('type') == 'surgical' ? 'selected' : '' }}>Cirugia</option>
                        <option value="emergency" {{ old('type') == 'emergency' ? 'selected' : '' }}>Urgencia</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de consulta</label>
                    <input type="text" name="reason" value="{{ old('reason') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('appointments.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Crear turno</button>
        </div>
    </form>

    @if($showDoctorSelect)
        <script>
            // Auto-select the doctor based on the selected patient's primary doctor.
            (function () {
                const patientSelect = document.getElementById('patient_select');
                const doctorSelect = document.getElementById('doctor_select');

                if (patientSelect && doctorSelect) {
                    patientSelect.addEventListener('change', function () {
                        const option = this.options[this.selectedIndex];
                        const doctorId = option?.dataset.doctorId || '';
                        if (doctorId) {
                            doctorSelect.value = doctorId;
                        }
                    });
                }
            })();
        </script>
    @endif
</x-layouts.tenant>
