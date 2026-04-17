<x-layouts.admin :title="'Editar Doctor'">
    <div class="mb-6">
        <a href="{{ route('admin.doctors.index') }}" class="text-blue-400 hover:underline text-sm">&larr; Volver a doctores</a>
        <h2 class="text-2xl font-bold text-white mt-2">Editar Doctor</h2>
    </div>

    @php
        $currentRole = $doctor->roles->first()?->name ?? 'doctor_admin';
    @endphp

    <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">Datos del doctor</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nombre completo *</label>
                    <input type="text" name="name" value="{{ old('name', $doctor->name) }}" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Especialidad *</label>
                    <select name="specialty" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar...</option>
                        @foreach(config('specialties') as $key => $spec)
                            <option value="{{ $key }}" {{ old('specialty', $doctor->specialty) === $key ? 'selected' : '' }}>
                                {{ $spec['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('specialty') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Correo electronico *</label>
                    <input type="email" name="email" value="{{ old('email', $doctor->email) }}" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                    @error('email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Telefono</label>
                    <input type="tel" name="phone" value="{{ old('phone', $doctor->phone) }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Cedula profesional</label>
                    <input type="text" name="professional_license" value="{{ old('professional_license', $doctor->professional_license) }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Rol *</label>
                    <select name="role" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="doctor_admin" {{ old('role', $currentRole) === 'doctor_admin' ? 'selected' : '' }}>Doctor Admin</option>
                        <option value="doctor_associate" {{ old('role', $currentRole) === 'doctor_associate' ? 'selected' : '' }}>Doctor Asociado</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Admin: gestiona staff y configuracion. Asociado: solo atencion clinica y financiera.</p>
                    @error('role') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Estado *</label>
                    <select name="status" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="active" {{ old('status', $doctor->status) === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="passive" {{ old('status', $doctor->status) === 'passive' ? 'selected' : '' }}>Pasivo</option>
                        <option value="inactive" {{ old('status', $doctor->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Pasivo: no puede operar pero cuenta en reparto de gastos. Inactivo: sin acceso.</p>
                    @error('status') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">Asignar a clinica(s) *</h3>
            <p class="text-sm text-gray-400 mb-4">Selecciona en que clinica(s) trabaja este doctor. La primera seleccionada sera su clinica principal.</p>
            <div class="space-y-2">
                @foreach($clinics as $clinic)
                    <label class="flex items-center p-3 bg-gray-700/50 rounded-md hover:bg-gray-700 cursor-pointer">
                        <input type="checkbox" name="clinic_ids[]" value="{{ $clinic->id }}"
                               {{ in_array($clinic->id, old('clinic_ids', $doctorClinicIds)) ? 'checked' : '' }}
                               class="rounded border-gray-500 text-blue-600 focus:ring-blue-500 bg-gray-600">
                        <span class="ml-3 text-sm text-gray-200">{{ $clinic->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('clinic_ids') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">Cambiar contrasena</h3>
            <p class="text-sm text-gray-400 mb-4">Dejar en blanco para mantener la contrasena actual.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nueva contrasena</label>
                    <input type="password" name="password"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                    @error('password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Confirmar contrasena</label>
                    <input type="password" name="password_confirmation"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.doctors.index') }}" class="px-4 py-2 border border-gray-600 rounded-md text-gray-300 hover:bg-gray-700">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Guardar cambios</button>
        </div>
    </form>
</x-layouts.admin>
