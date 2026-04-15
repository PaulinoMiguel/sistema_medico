<x-layouts.tenant :title="$patient->full_name">
    <div class="mb-6">
        @if(request('from') === 'consultation' && request('consultation_id'))
            <a href="{{ route('consultations.edit', request('consultation_id')) }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a la consulta</a>
        @elseif(request('from') === 'prescription' && request('prescription_id'))
            <a href="{{ route('prescriptions.show', request('prescription_id')) }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a la receta</a>
        @else
            <a href="{{ route('patients.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a pacientes</a>
        @endif
    </div>

    {{-- Patient Header --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div class="flex items-start gap-4">
                {{-- Photo --}}
                <div class="flex-shrink-0">
                    @if($patient->photo_url)
                        <img src="{{ $patient->photo_url }}" alt="{{ $patient->full_name }}"
                             class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                    @else
                        <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center border-2 border-gray-200">
                            <span class="text-2xl font-bold text-blue-600">{{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}</span>
                        </div>
                    @endif
                    {{-- Upload/change photo --}}
                    <div class="mt-2 text-center">
                        <button type="button" onclick="document.getElementById('photo-input').click()"
                                class="text-xs text-blue-600 hover:underline">
                            {{ $patient->photo_url ? 'Cambiar' : 'Agregar foto' }}
                        </button>
                        @if($patient->photo_url)
                            <form action="{{ route('patients.photo.delete', $patient) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline ml-1">Quitar</button>
                            </form>
                        @endif
                    </div>
                    <form id="photo-form" action="{{ route('patients.photo', $patient) }}" method="POST" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp"
                               onchange="document.getElementById('photo-form').submit()">
                    </form>
                </div>

                {{-- Info --}}
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $patient->full_name }}</h2>
                    <p class="text-gray-500">Expediente: {{ $patient->medical_record_number }}</p>
                    <div class="mt-2 flex gap-4 text-sm text-gray-600">
                        <span>{{ $patient->age }} años</span>
                        <span>{{ $patient->gender == 'male' ? 'Masculino' : ($patient->gender == 'female' ? 'Femenino' : 'Otro') }}</span>
                        @if($patient->blood_type)
                            <span>Sangre: {{ $patient->blood_type }}</span>
                        @endif
                        @if($patient->phone)
                            <span>Tel: {{ $patient->phone }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('patients.edit', $patient) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Editar</a>
                @if(auth()->user()->specialty === 'pediatrics' && $patient->date_of_birth && $patient->gender)
                    <a href="{{ route('patients.growth', $patient) }}" class="px-4 py-2 border border-emerald-300 text-emerald-700 rounded-md text-sm hover:bg-emerald-50">Crecimiento</a>
                @endif
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

    {{-- Recent Prescriptions --}}
    @if(auth()->user()->isDoctor())
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Ultimas recetas</h3>
            <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}" class="text-blue-600 hover:underline text-sm">+ Nueva receta</a>
        </div>
        @php
            $prescriptions = $patient->prescriptions()
                ->where('clinic_id', session('active_clinic_id'))
                ->with('items')
                ->orderByDesc('prescription_date')
                ->limit(5)
                ->get();
        @endphp
        @if($prescriptions->isEmpty())
            <p class="text-gray-500 text-sm">Sin recetas registradas.</p>
        @else
            <div class="space-y-3">
                @foreach($prescriptions as $rx)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $rx->prescription_number }}</p>
                        <p class="text-xs text-gray-500">{{ $rx->prescription_date->format('d/m/Y') }} - {{ $rx->items->count() }} medicamento(s)</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @php
                            $statusColors = ['active'=>'bg-green-100 text-green-800','expired'=>'bg-yellow-100 text-yellow-800','cancelled'=>'bg-red-100 text-red-800'];
                            $statusLabels = ['active'=>'Activa','expired'=>'Vencida','cancelled'=>'Cancelada'];
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$rx->status] }}">{{ $statusLabels[$rx->status] }}</span>
                        <a href="{{ route('prescriptions.show', $rx) }}" class="text-blue-600 hover:underline text-xs">Ver</a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif
</x-layouts.tenant>
