<x-layouts.tenant :title="'Dashboard'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Bienvenido, {{ auth()->user()->name }}</h2>
        <p class="text-gray-500">{{ now()->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Turnos hoy</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $todayAppointments->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total pacientes</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalPatients }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Turnos pendientes</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingAppointments }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Appointments --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Turnos de hoy</h3>
            <a href="{{ route('appointments.create') }}" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-md hover:bg-blue-700">
                + Nuevo turno
            </a>
        </div>

        @if($todayAppointments->isEmpty())
            <div class="p-6 text-center text-gray-500">
                No hay turnos programados para hoy.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($todayAppointments as $appointment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $appointment->scheduled_at->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <a href="{{ route('patients.show', $appointment->patient) }}" class="text-blue-600 hover:underline">
                                    {{ $appointment->patient->full_name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @php
                                    $types = [
                                        'first_visit' => 'Primera vez',
                                        'follow_up' => 'Control',
                                        'pre_operative' => 'Pre-quirurgico',
                                        'post_operative' => 'Post-quirurgico',
                                        'urodynamic_study' => 'Urodinamia',
                                        'procedure' => 'Procedimiento',
                                        'emergency' => 'Urgencia',
                                        'surgical' => 'Cirugia',
                                    ];
                                @endphp
                                {{ $types[$appointment->type] ?? $appointment->type }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'scheduled' => 'bg-gray-100 text-gray-800',
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'in_waiting_room' => 'bg-yellow-100 text-yellow-800',
                                        'in_progress' => 'bg-purple-100 text-purple-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'no_show' => 'bg-red-100 text-red-800',
                                    ];
                                    $statusLabels = [
                                        'scheduled' => 'Programado',
                                        'confirmed' => 'Confirmado',
                                        'in_waiting_room' => 'En espera',
                                        'in_progress' => 'En consulta',
                                        'completed' => 'Completado',
                                        'cancelled' => 'Cancelado',
                                        'no_show' => 'No asistio',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$appointment->status] ?? '' }}">
                                    {{ $statusLabels[$appointment->status] ?? $appointment->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('appointments.show', $appointment) }}" class="text-blue-600 hover:underline">Ver</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.tenant>
