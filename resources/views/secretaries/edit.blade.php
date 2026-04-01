<x-layouts.tenant :title="'Editar Secretaria'">
    <div class="mb-6">
        <a href="{{ route('secretaries.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a secretarias</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Editar: {{ $secretary->name }}</h2>
    </div>

    <form method="POST" action="{{ route('secretaries.update', $secretary) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos de acceso</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                    <input type="text" name="name" value="{{ old('name', $secretary->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="tel" name="phone" value="{{ old('phone', $secretary->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electronico *</label>
                    <input type="email" name="email" value="{{ old('email', $secretary->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contrasena</label>
                    <input type="password" name="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Dejar vacio para no cambiar">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contrasena</label>
                    <input type="password" name="password_confirmation"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Clinica(s) asignada(s)</h3>
            <div class="space-y-3">
                @foreach($clinics as $clinic)
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="clinic_ids[]" value="{{ $clinic->id }}"
                               {{ in_array($clinic->id, old('clinic_ids', $assignedClinicIds)) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">{{ $clinic->name }}</p>
                            @if($clinic->address)
                                <p class="text-xs text-gray-500">{{ $clinic->address }}</p>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
            @error('clinic_ids')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('secretaries.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Guardar cambios</button>
        </div>
    </form>
</x-layouts.tenant>
