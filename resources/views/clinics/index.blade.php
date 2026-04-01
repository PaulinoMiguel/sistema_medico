<x-layouts.tenant :title="'Clinicas'">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Mis Clinicas</h2>
        <a href="{{ route('clinics.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Nueva clinica
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($clinics as $clinic)
            <div class="bg-white rounded-lg shadow p-6 {{ !$clinic->is_active ? 'opacity-60' : '' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $clinic->name }}</h3>
                        @php
                            $typeLabels = ['office' => 'Consultorio', 'hospital' => 'Hospital', 'surgical_center' => 'Centro quirurgico'];
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                            {{ $typeLabels[$clinic->type] ?? $clinic->type }}
                        </span>
                    </div>
                    @if(!$clinic->is_active)
                        <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-600">Inactiva</span>
                    @endif
                </div>

                <dl class="space-y-2 text-sm text-gray-600 mb-4">
                    @if($clinic->address)
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $clinic->address }}
                        </div>
                    @endif
                    @if($clinic->phone)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $clinic->phone }}
                        </div>
                    @endif
                </dl>

                <div class="flex items-center justify-between text-sm border-t pt-4">
                    <div class="flex gap-4 text-gray-500">
                        <span>{{ $clinic->users_count }} usuarios</span>
                        <span>{{ $clinic->patients_count }} pacientes</span>
                    </div>
                    <a href="{{ route('clinics.edit', $clinic) }}" class="text-blue-600 hover:underline">Editar</a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow p-8 text-center text-gray-500">
                <p class="mb-4">No tienes clinicas creadas.</p>
                <a href="{{ route('clinics.create') }}" class="text-blue-600 hover:underline">Crear tu primera clinica</a>
            </div>
        @endforelse
    </div>
</x-layouts.tenant>
