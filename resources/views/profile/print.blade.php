<x-layouts.tenant title="Mi perfil de impresion">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Mi perfil de impresion</h2>
            <p class="text-gray-500 text-sm mt-1">Estos datos se muestran en las cabeceras de ordenes diagnosticas, recetas y demas documentos impresos. Cada doctor configura los suyos.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Logo --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">Logo</h3>
            <div class="flex items-start gap-6">
                <div class="w-32 h-32 border border-gray-300 rounded-md flex items-center justify-center bg-gray-50 overflow-hidden">
                    @if($user->print_logo_path)
                        <img src="{{ asset('storage/' . $user->print_logo_path) }}" alt="Logo" class="max-w-full max-h-full">
                    @else
                        <span class="text-xs text-gray-400 text-center px-2">Sin logo</span>
                    @endif
                </div>
                <div class="flex-1">
                    <form method="POST" action="{{ route('profile.print.logo') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subir logo (PNG, JPG, WEBP, SVG. Max 2MB)</label>
                            <input type="file" name="print_logo" accept="image/*" required
                                   class="block w-full text-sm border border-gray-300 rounded-md file:bg-blue-50 file:border-0 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700">
                            @error('print_logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                            Guardar logo
                        </button>
                    </form>
                    @if($user->print_logo_path)
                        <form method="POST" action="{{ route('profile.print.logo.delete') }}" class="mt-2">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Eliminar el logo?')" class="text-sm text-red-600 hover:underline">
                                Eliminar logo
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Datos textuales --}}
        <form method="POST" action="{{ route('profile.print.update') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf @method('PUT')
            <h3 class="font-semibold text-gray-800">Datos para cabecera</h3>
            <p class="text-xs text-gray-500 -mt-2">Tu nombre, exequatur y telefono se toman de "Mi perfil". Estos campos son adicionales para los documentos impresos.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Direccion del consultorio</label>
                <input type="text" name="print_address" value="{{ old('print_address', $user->print_address) }}" maxlength="255"
                       placeholder="Ej: Av. Independencia 123, Local 4, Santo Domingo"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('print_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sitio web (opcional)</label>
                <input type="text" name="print_website" value="{{ old('print_website', $user->print_website) }}" maxlength="255"
                       placeholder="Ej: www.uroperalta.do"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('print_website') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Texto adicional de cabecera (opcional)</label>
                <textarea name="print_extra_header" rows="3" maxlength="1000"
                          placeholder="Ej: Especialista en endourologia y urologia funcional"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('print_extra_header', $user->print_extra_header) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Aparece debajo del nombre y exequatur en los documentos impresos.</p>
                @error('print_extra_header') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</x-layouts.tenant>
