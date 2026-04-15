<x-layouts.tenant :title="'Configuracion'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Configuracion de la instalacion</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Marca visual y modulos habilitados.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded p-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Marca</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre *</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name', $settings->brand_name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md">
                    @error('brand_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subtitulo</label>
                    <input type="text" name="brand_tagline" value="{{ old('brand_tagline', $settings->brand_tagline) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color primario *</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}"
                               class="h-10 w-16 border border-gray-300 rounded">
                        <span class="text-sm text-gray-500 font-mono">{{ $settings->primary_color }}</span>
                    </div>
                    @error('primary_color') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo</label>
                    @if($settings->logoUrl())
                        <div class="flex items-center gap-3 mb-2">
                            <img src="{{ $settings->logoUrl() }}" alt="logo" class="h-10 border rounded">
                            <label class="text-xs text-red-600">
                                <input type="checkbox" name="remove_logo" value="1"> Eliminar logo
                            </label>
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*"
                           class="w-full text-sm text-gray-700 dark:text-gray-300">
                    @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Modulos opcionales</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Al deshabilitar un modulo se oculta del menu y sus rutas devuelven 404.
            </p>

            @php
                $labels = [
                    'prescriptions' => 'Recetas',
                    'expenses' => 'Gastos y resumen financiero',
                    'cash_register' => 'Caja (cortes de caja)',
                    'services' => 'Catalogo de servicios',
                ];
            @endphp

            <div class="space-y-3">
                @foreach($moduleKeys as $key)
                    @php $enabled = $settings->moduleEnabled($key); @endphp
                    <label class="flex items-center cursor-pointer">
                        <input type="hidden" name="modules[{{ $key }}]" value="0">
                        <input type="checkbox" name="modules[{{ $key }}]" value="1" {{ $enabled ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $labels[$key] ?? $key }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Guardar cambios</button>
        </div>
    </form>
</x-layouts.tenant>
