<x-layouts.tenant :title="'Nuevo Paciente'">
    <div class="mb-6">
        <a href="{{ route('patients.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a pacientes</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Nuevo Paciente</h2>
    </div>

    {{-- Duplicate detection banner (server-side) --}}
    @if(session('duplicate_patient_id'))
        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1">
                    <p class="font-semibold text-yellow-800">Este paciente ya existe en el sistema</p>
                    <p class="text-sm text-yellow-700 mt-1">
                        <span class="font-medium">{{ session('duplicate_patient_name') }}</span> — {{ session('duplicate_patient_doc') }}
                    </p>
                    <form method="POST" action="{{ route('patients.associate', session('duplicate_patient_id')) }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="doctor_id" value="{{ session('duplicate_doctor_id') }}">
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium">
                            Asociar a mi lista de pacientes
                        </button>
                        <span class="text-xs text-yellow-600 ml-2">En vez de crear uno nuevo</span>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Duplicate detection banner (AJAX / client-side) --}}
    <div id="duplicate-banner" class="hidden bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1">
                <p class="font-semibold text-yellow-800">Este paciente ya existe en el sistema</p>
                <p class="text-sm text-yellow-700 mt-1">
                    <span id="dup-name" class="font-medium"></span> — <span id="dup-doc"></span> — Nac: <span id="dup-dob"></span>
                </p>
                <form id="associate-form" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="doctor_id" id="assoc-doctor-id">
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium">
                        Asociar a mi lista de pacientes
                    </button>
                    <span class="text-xs text-yellow-600 ml-2">En vez de crear uno nuevo</span>
                </form>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('patients.store') }}" enctype="multipart/form-data" id="patient-form">
        @csrf

        @if($doctors->isNotEmpty())
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Doctor responsable</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Doctor *</label>
                    <select name="doctor_id" id="doctor_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar doctor...</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->name }}@if($doctor->specialty) — {{ $doctor->specialty }}@endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">El paciente quedara asignado a este doctor.</p>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos personales</h3>

            {{-- Photo --}}
            <div class="mb-6 flex items-center gap-4">
                <div id="photo-preview" class="w-20 h-20 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto del paciente</label>
                    <input type="file" name="photo" id="photo-input" accept="image/jpeg,image/png,image/webp"
                           class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG o WEBP. Maximo 2MB. Opcional.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido paterno *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido materno</label>
                    <input type="text" name="second_last_name" value="{{ old('second_last_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Genero *</label>
                    <select name="gender" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar...</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Masculino</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Femenino</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label>
                    <select name="document_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="cedula" {{ old('document_type', 'cedula') == 'cedula' ? 'selected' : '' }}>Cedula</option>
                        <option value="passport" {{ old('document_type') == 'passport' ? 'selected' : '' }}>Pasaporte</option>
                        <option value="other" {{ old('document_type') == 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de documento</label>
                    <input type="text" name="document_number" value="{{ old('document_number') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de sangre</label>
                    <select name="blood_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Desconocido</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                            <option value="{{ $bt }}" {{ old('blood_type') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Contacto</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono secundario</label>
                    <input type="tel" name="secondary_phone" value="{{ old('secondary_phone') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electronico</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado/Provincia</label>
                    <input type="text" name="state" value="{{ old('state') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo postal</label>
                    <input type="text" name="zip_code" value="{{ old('zip_code') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informacion adicional</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contacto de emergencia</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tel. emergencia</label>
                    <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ocupacion</label>
                    <input type="text" name="occupation" value="{{ old('occupation') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Aseguradora</label>
                    <input type="text" name="insurance_provider" value="{{ old('insurance_provider') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. poliza</label>
                    <input type="text" name="insurance_policy_number" value="{{ old('insurance_policy_number') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referido por</label>
                    <input type="text" name="referred_by" value="{{ old('referred_by') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('patients.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Guardar paciente</button>
        </div>
    </form>

    <script>
        document.getElementById('photo-input').addEventListener('change', function () {
            const file = this.files[0];
            const preview = document.getElementById('photo-preview');
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="w-20 h-20 rounded-full object-cover">';
                };
                reader.readAsDataURL(file);
            }
        });

        // Duplicate detection by document number
        let debounceTimer;
        const docInput = document.querySelector('input[name="document_number"]');
        const banner = document.getElementById('duplicate-banner');

        if (docInput) {
            docInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const val = this.value.trim();

                if (val.length < 3) {
                    banner.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch('{{ route("patients.check-duplicate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ document_number: val })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.found) {
                            document.getElementById('dup-name').textContent = data.patient.full_name;
                            document.getElementById('dup-doc').textContent = data.patient.document_number;
                            document.getElementById('dup-dob').textContent = data.patient.date_of_birth;
                            const form = document.getElementById('associate-form');
                            form.action = '/patients/' + data.patient.id + '/associate';
                            const doctorSelect = document.getElementById('doctor_id');
                            const assocDoctor = document.getElementById('assoc-doctor-id');
                            if (doctorSelect) {
                                assocDoctor.value = doctorSelect.value;
                            }
                            banner.classList.remove('hidden');
                        } else {
                            banner.classList.add('hidden');
                        }
                    });
                }, 500);
            });
        }
    </script>
</x-layouts.tenant>
