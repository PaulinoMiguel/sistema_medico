<x-layouts.tenant :title="'Secretarias'">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Secretarias</h2>
        <a href="{{ route('secretaries.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Nueva secretaria
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($secretaries->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="mb-4">No tienes secretarias creadas.</p>
                <a href="{{ route('secretaries.create') }}" class="text-blue-600 hover:underline">Crear la primera secretaria</a>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clinica(s)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($secretaries as $secretary)
                    <tr class="hover:bg-gray-50 {{ !$secretary->isActive() ? 'opacity-50' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $secretary->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $secretary->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $secretary->phone ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @foreach($secretary->clinics as $clinic)
                                <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-blue-50 text-blue-700 mr-1">
                                    {{ $clinic->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $secretary->isActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $secretary->isActive() ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('secretaries.edit', $secretary) }}" class="text-blue-600 hover:underline">Editar</a>
                            <form action="{{ route('secretaries.toggle', $secretary) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="{{ $secretary->isActive() ? 'text-red-600' : 'text-green-600' }} hover:underline">
                                    {{ $secretary->isActive() ? 'Desactivar' : 'Activar' }}
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
