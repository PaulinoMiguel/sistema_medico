<x-layouts.admin :title="'Nueva Clinica'">
    <div class="mb-6">
        <a href="{{ route('admin.clinics.index') }}" class="text-blue-400 hover:underline text-sm">&larr; Volver a clinicas</a>
        <h2 class="text-2xl font-bold text-white mt-2">Nueva Clinica</h2>
    </div>

    <form method="POST" action="{{ route('admin.clinics.store') }}">
        @csrf

        <div class="bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">Datos de la clinica</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ej: Consultorio Centro, Hospital Angeles...">
                    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">RFC / ID Fiscal</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id') }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Telefono</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Direccion</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Ciudad</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Estado/Provincia</label>
                    <input type="text" name="state" value="{{ old('state') }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.clinics.index') }}" class="px-4 py-2 border border-gray-600 rounded-md text-gray-300 hover:bg-gray-700">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Crear clinica</button>
        </div>
    </form>
</x-layouts.admin>
