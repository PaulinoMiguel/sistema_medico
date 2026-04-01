<x-layouts.tenant :title="$patient->full_name">
    <div class="mb-6">
        <a href="{{ route('patients.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a pacientes</a>
    </div>

    {{-- Patient Header --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $patient->full_name }}</h2>
                <p class="text-gray-500">Expediente: {{ $patient->medical_record_number }}</p>
                <div class="mt-2 flex gap-4 text-sm text-gray-600">
                    <span>{{ $patient->age }} anios</span>
                    <span>{{ $patient->gender == 'male' ? 'Masculino' : ($patient->gender == 'female' ? 'Femenino' : 'Otro') }}</span>
                    @if($patient->blood_type)
                        <span>Sangre: {{ $patient->blood_type }}</span>
                    @endif
                    @if($patient->phone)
                        <span>Tel: {{ $patient->phone }}</span>
                    @endif
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('patients.edit', $patient) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Editar</a>
                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">+ Nuevo turno</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Contact Info --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informacion de contacto</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="text-gray-800">{{ $patient->email ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Telefono</dt>
                    <dd class="text-gray-800">{{ $patient->phone ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Direccion</dt>
                    <dd class="text-gray-800">{{ $patient->address ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Aseguradora</dt>
                    <dd class="text-gray-800">{{ $patient->insurance_provider ?? 'Particular' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Emergencia</dt>
                    <dd class="text-gray-800">{{ $patient->emergency_contact_name ?? '-' }} {{ $patient->emergency_contact_phone ? '('.$patient->emergency_contact_phone.')' : '' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Recent Appointments --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ultimos turnos</h3>
            @if($patient->appointments->isEmpty())
                <p class="text-gray-500 text-sm">Sin turnos registrados.</p>
            @else
                <div class="space-y-3">
                    @foreach($patient->appointments as $apt)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $apt->scheduled_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-500">{{ $apt->doctor->name ?? 'Doctor' }}</p>
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
