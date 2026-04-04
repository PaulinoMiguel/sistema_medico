<x-layouts.admin :title="'Mi Perfil'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">Mi Perfil</h2>
        <p class="text-gray-400">Configuracion de cuenta del administrador.</p>
    </div>

    <div class="max-w-xl">
        {{-- Info --}}
        <div class="bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">Datos de cuenta</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-400 uppercase">Nombre</p>
                    <p class="text-sm text-white">{{ $admin->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Correo electronico</p>
                    <p class="text-sm text-white">{{ $admin->email }}</p>
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <form method="POST" action="{{ route('admin.profile.password') }}">
            @csrf @method('PUT')
            <div class="bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Cambiar contrasena</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Contrasena actual *</label>
                        <input type="password" name="current_password" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                        @error('current_password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nueva contrasena *</label>
                        <input type="password" name="password" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                        @error('password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Confirmar contrasena *</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        Cambiar contrasena
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
