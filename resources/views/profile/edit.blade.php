<x-layouts.tenant :title="'Mi Perfil'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Mi Perfil</h2>
        <p class="text-gray-500">Configura tu informacion personal y credenciales.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Photo --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="mb-4">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                             class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-blue-100">
                    @else
                        <div class="w-32 h-32 rounded-full mx-auto bg-blue-100 flex items-center justify-center border-4 border-blue-50">
                            <span class="text-4xl font-bold text-blue-600">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                    @endif
                </div>

                <h3 class="text-lg font-semibold text-gray-800">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 capitalize">{{ $user->specialty ?? $user->role }}</p>

                <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <label class="block">
                        <span class="sr-only">Elegir foto</span>
                        <input type="file" name="profile_photo" accept="image/*"
                               onchange="this.form.submit()"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </label>
                    @error('profile_photo')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </form>

                @if($user->profile_photo_url)
                    <form method="POST" action="{{ route('profile.photo.delete') }}" class="mt-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Eliminar foto</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Personal Info + Password --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal Data --}}
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos personales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo electronico *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                            <input type="text" name="specialty" value="{{ old('specialty', $user->specialty) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('specialty') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cedula profesional</label>
                            <input type="text" name="professional_license" value="{{ old('professional_license', $user->professional_license) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Acerca de mi</label>
                        <textarea name="bio" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Breve descripcion profesional...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </form>

            {{-- Change Password --}}
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf @method('PUT')
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Cambiar contrasena</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contrasena actual *</label>
                            <input type="password" name="current_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contrasena *</label>
                            <input type="password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contrasena *</label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900 font-medium">
                            Cambiar contrasena
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.tenant>
