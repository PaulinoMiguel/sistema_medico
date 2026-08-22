<x-layouts.tenant title="Mi perfil de impresión">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Mi perfil de impresión</h2>
            <p class="text-gray-500 text-sm mt-1">Estos datos se muestran en las cabeceras de ordenes diagnosticas, recetas y demas documentos impresos. Cada doctor configura los suyos.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Los dos logos de la cabecera impresa: el del doctor a la izquierda
             y el del hospital o centro a la derecha. Mismo formulario, cambia
             el lado que se manda en la ruta. --}}
        @php
            $logos = [
                'left' => ['titulo' => 'Logo (izquierda)', 'ayuda' => 'El tuyo. Aparece a la izquierda de la cabecera.', 'ruta' => $user->print_logo_path],
                'right' => ['titulo' => 'Logo del hospital o centro (derecha)', 'ayuda' => 'Opcional. Aparece a la derecha, al mismo tamaño que el tuyo.', 'ruta' => $user->print_logo_right_path],
            ];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            @foreach($logos as $lado => $logo)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-800">{{ $logo['titulo'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1 mb-3">{{ $logo['ayuda'] }}</p>
                    <div class="flex items-start gap-6">
                        <div class="w-32 h-32 flex-shrink-0 border border-gray-300 rounded-md flex items-center justify-center bg-gray-50 overflow-hidden">
                            @if($logo['ruta'])
                                <img src="{{ asset('storage/' . $logo['ruta']) }}" alt="Logo" class="max-w-full max-h-full">
                            @else
                                <span class="text-xs text-gray-400 text-center px-2">Sin logo</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <form method="POST" action="{{ route('profile.print.logo', $lado) }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Subir (PNG, JPG, WEBP, SVG. Max 2MB)</label>
                                    <input type="file" name="print_logo" accept="image/*" required
                                           class="block w-full text-sm border border-gray-300 rounded-md file:bg-blue-50 file:border-0 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700">
                                </div>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                    Guardar logo
                                </button>
                            </form>
                            @if($logo['ruta'])
                                <form method="POST" action="{{ route('profile.print.logo.delete', $lado) }}" class="mt-2">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Eliminar este logo?')" class="text-sm text-red-600 hover:underline">
                                        Eliminar logo
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @error('print_logo') <p class="mb-6 text-sm text-red-600">{{ $message }}</p> @enderror

        {{-- Firma: se imprime en el acta de entrega de caja --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="font-semibold text-gray-800">Firma</h3>
            <p class="text-xs text-gray-500 mt-1 mb-3">
                Se imprime en el acta de entrega de caja. Sube una foto o escaneo de tu firma,
                preferiblemente con fondo blanco.
            </p>
            <div class="flex items-start gap-6">
                <div class="w-48 h-24 flex-shrink-0 border border-gray-300 rounded-md flex items-center justify-center bg-gray-50 overflow-hidden">
                    @if($user->digital_signature_path)
                        <img src="{{ asset('storage/' . $user->digital_signature_path) }}" alt="Firma" class="max-w-full max-h-full">
                    @else
                        <span class="text-xs text-gray-400">Sin firma</span>
                    @endif
                </div>
                <div class="flex-1">
                    <form method="POST" action="{{ route('profile.signature') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file" name="signature" accept="image/*" required
                               class="block w-full text-sm border border-gray-300 rounded-md file:bg-blue-50 file:border-0 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700">
                        @error('signature') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                            Guardar firma
                        </button>
                    </form>
                    @if($user->digital_signature_path)
                        <form method="POST" action="{{ route('profile.signature.delete') }}" class="mt-2">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Eliminar la firma?')" class="text-sm text-red-600 hover:underline">
                                Eliminar firma
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- PIN de autorizacion: solo doctores --}}
        @if($user->isDoctor())
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="font-semibold text-gray-800">PIN de autorización</h3>
            <p class="text-xs text-gray-500 mt-1 mb-1">
                Lo usas para recibir la caja en la pantalla de la secretaria, sin tener que
                iniciar sesión con tu cuenta.
            </p>
            <p class="text-xs text-gray-500 mb-4">
                <strong>No es tu contraseña de acceso.</strong> Es un código aparte precisamente
                para que puedas teclearlo delante de otra persona: si alguien lo ve, lo único que
                puede hacer es recibir una caja, no entrar al sistema.
                @if($user->hasAuthorizationPin())
                    <span class="text-green-700">Ya tienes un PIN configurado.</span>
                @else
                    <span class="text-amber-700">Todavía no has configurado tu PIN.</span>
                @endif
            </p>
            <form method="POST" action="{{ route('profile.authorization-pin') }}" class="space-y-3 max-w-md">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tu contraseña actual *</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PIN nuevo *</label>
                        <input type="password" name="authorization_pin" required inputmode="numeric" autocomplete="off"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="4 a 8 dígitos">
                        @error('authorization_pin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Repetir PIN *</label>
                        <input type="password" name="authorization_pin_confirmation" required inputmode="numeric" autocomplete="off"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                    Guardar PIN
                </button>
            </form>
        </div>
        @endif

        {{-- Datos textuales --}}
        <form method="POST" action="{{ route('profile.print.update') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf @method('PUT')
            <h3 class="font-semibold text-gray-800">Datos para cabecera</h3>
            <p class="text-xs text-gray-500 -mt-2">Tu nombre, exequatur y teléfono se toman de "Mi perfil". Estos campos son adicionales para los documentos impresos.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Consultorio o clínica</label>
                <input type="text" name="print_address" value="{{ old('print_address', $user->print_address) }}" maxlength="255"
                       placeholder="Ej: HHWC, consultorio 301"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Última línea de la cabecera impresa. Escríbelo tal como quieres que aparezca. Si lo dejas vacío se usa el nombre de la clínica.</p>
                @error('print_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            @if($user->clinics->count() > 1)
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700">Línea distinta por clínica</h4>
                <p class="mt-1 mb-3 text-xs text-gray-500">
                    Si atiendes en varias clínicas y quieres que la receta imprima algo distinto en cada una,
                    escríbelo aquí. Lo que dejes vacío usa la línea de arriba. Todo lo demás de la cabecera
                    (logo, nombre, especialidad, correo y teléfonos) es igual en todas.
                </p>
                <div class="space-y-3">
                    @foreach($user->clinics as $clinic)
                        @php
                            $overrides = json_decode($clinic->pivot->print_overrides ?? '', true) ?: [];
                        @endphp
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">{{ $clinic->name }}</label>
                            <input type="text" name="clinic_print_address[{{ $clinic->id }}]" maxlength="255"
                                   value="{{ old('clinic_print_address.' . $clinic->id, $overrides['print_address'] ?? '') }}"
                                   placeholder="Dejar vacío para usar la línea de arriba"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

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
                          placeholder="Ej: Especialista en endourología y urología funcional"
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
