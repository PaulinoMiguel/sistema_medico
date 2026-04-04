<x-layouts.tenant :title="'Servicios'">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Catalogo de Servicios</h2>
            <p class="text-gray-500 text-sm">Configura los servicios y precios de tu clinica.</p>
        </div>
        <a href="{{ route('services.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Nuevo servicio
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($services->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="mb-4">No hay servicios configurados para esta clinica.</p>
                <a href="{{ route('services.create') }}" class="text-blue-600 hover:underline">Crear el primer servicio</a>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($services as $service)
                    <tr class="hover:bg-gray-50 {{ !$service->is_active ? 'opacity-50' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $service->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $service->description ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right font-mono">${{ number_format($service->price, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $service->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('services.edit', $service) }}" class="text-blue-600 hover:underline">Editar</a>
                            <form action="{{ route('services.toggle', $service) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="{{ $service->is_active ? 'text-red-600' : 'text-green-600' }} hover:underline">
                                    {{ $service->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.tenant>
