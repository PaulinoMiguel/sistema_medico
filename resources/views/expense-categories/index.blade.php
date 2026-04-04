<x-layouts.tenant :title="'Categorias de Gastos'">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Categorias de Gastos</h2>
            <p class="text-gray-500 text-sm">Organiza los gastos de tu clinica por categoria.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Create form --}}
        <div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Nueva categoria</h3>
                <form method="POST" action="{{ route('expense-categories.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: Renta, Servicios, Insumos...">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                        Crear categoria
                    </button>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if($categories->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <p>No hay categorias creadas.</p>
                        <p class="text-sm mt-2">Crea categorias como: Renta, Servicios, Insumos medicos, Nomina, etc.</p>
                    </div>
                @else
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gastos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($categories as $category)
                            <tr class="hover:bg-gray-50 {{ !$category->is_active ? 'opacity-50' : '' }}">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 text-right">{{ $category->expenses_count }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <form action="{{ route('expense-categories.toggle', $category) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="{{ $category->is_active ? 'text-red-600' : 'text-green-600' }} hover:underline">
                                            {{ $category->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-layouts.tenant>
