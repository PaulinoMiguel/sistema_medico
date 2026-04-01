<x-layouts.tenant :title="'Turnos'">
    @php
        $statusColors = [
            'scheduled' => 'bg-gray-200 text-gray-800',
            'confirmed' => 'bg-blue-200 text-blue-800',
            'in_waiting_room' => 'bg-yellow-200 text-yellow-800',
            'in_progress' => 'bg-purple-200 text-purple-800',
            'completed' => 'bg-green-200 text-green-800',
            'cancelled' => 'bg-red-200 text-red-800 line-through',
            'no_show' => 'bg-red-100 text-red-600',
        ];
        $typeLabels = [
            'first_visit' => '1ra vez',
            'follow_up' => 'Control',
            'pre_operative' => 'Pre-Qx',
            'post_operative' => 'Post-Qx',
            'urodynamic_study' => 'Urodin.',
            'procedure' => 'Proced.',
            'emergency' => 'Urgencia',
            'surgical' => 'Cirugia',
        ];
    @endphp

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Turnos</h2>
        <a href="{{ route('appointments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
            + Nuevo turno
        </a>
    </div>

    {{-- Navigation bar --}}
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @php
                    $prevDate = $view === 'month' ? $date->copy()->subMonth()->toDateString() : $date->copy()->subWeek()->toDateString();
                    $nextDate = $view === 'month' ? $date->copy()->addMonth()->toDateString() : $date->copy()->addWeek()->toDateString();
                @endphp
                <a href="{{ route('appointments.index', ['view' => $view, 'date' => $prevDate]) }}"
                   class="p-2 hover:bg-gray-100 rounded text-gray-600">&larr;</a>
                <a href="{{ route('appointments.index', ['view' => $view, 'date' => today()->toDateString()]) }}"
                   class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md text-gray-700">Hoy</a>
                <a href="{{ route('appointments.index', ['view' => $view, 'date' => $nextDate]) }}"
                   class="p-2 hover:bg-gray-100 rounded text-gray-600">&rarr;</a>

                <span class="ml-4 text-lg font-semibold text-gray-800 capitalize">
                    @if($view === 'month')
                        {{ $date->translatedFormat('F Y') }}
                    @else
                        {{ $date->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->format('d') }} - {{ $date->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->translatedFormat('d \\d\\e F Y') }}
                    @endif
                </span>
            </div>

            <div class="flex bg-gray-100 rounded-md p-1">
                <a href="{{ route('appointments.index', ['view' => 'week', 'date' => $date->toDateString()]) }}"
                   class="px-3 py-1 text-sm rounded {{ $view === 'week' ? 'bg-white shadow text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Semana
                </a>
                <a href="{{ route('appointments.index', ['view' => 'month', 'date' => $date->toDateString()]) }}"
                   class="px-3 py-1 text-sm rounded {{ $view === 'month' ? 'bg-white shadow text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Mes
                </a>
            </div>
        </div>
    </div>

    @if($view === 'week')
        {{-- WEEKLY VIEW --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="grid grid-cols-7 border-b">
                @foreach($days as $key => $day)
                    <div class="p-3 text-center border-r last:border-r-0 {{ $day['isToday'] ? 'bg-blue-50' : '' }}">
                        <p class="text-xs text-gray-500 uppercase">{{ $day['date']->translatedFormat('D') }}</p>
                        <p class="text-lg font-semibold {{ $day['isToday'] ? 'text-blue-600' : 'text-gray-800' }}">
                            {{ $day['date']->format('d') }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $day['date']->translatedFormat('M') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 min-h-[500px]">
                @foreach($days as $key => $day)
                    <div class="border-r last:border-r-0 p-2 {{ $day['isToday'] ? 'bg-blue-50/30' : '' }}">
                        @foreach($day['appointments'] as $apt)
                            <a href="{{ route('appointments.show', $apt) }}"
                               class="block mb-1.5 p-2 rounded text-xs {{ $statusColors[$apt->status] ?? 'bg-gray-100' }} hover:opacity-80 transition">
                                <div class="font-semibold">{{ $apt->scheduled_at->format('H:i') }}</div>
                                <div class="truncate font-medium">{{ $apt->patient->last_name }}</div>
                                <div class="truncate text-[10px] opacity-75">{{ $typeLabels[$apt->type] ?? $apt->type }}</div>
                            </a>
                        @endforeach

                        @if($day['appointments']->isEmpty())
                            <div class="h-full flex items-center justify-center">
                                <a href="{{ route('appointments.create', ['date' => $key]) }}"
                                   class="text-gray-300 hover:text-blue-400 transition" title="Agregar turno">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

    @else
        {{-- MONTHLY VIEW --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="grid grid-cols-7 border-b bg-gray-50">
                @foreach(['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'] as $dayName)
                    <div class="p-3 text-center text-xs font-medium text-gray-500 uppercase">{{ $dayName }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7">
                @foreach($days as $key => $day)
                    <div class="border-r border-b last:border-r-0 min-h-[120px] p-2 {{ !$day['isCurrentMonth'] ? 'bg-gray-50/50' : '' }} {{ $day['isToday'] ? 'bg-blue-50/50' : '' }}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm {{ $day['isToday'] ? 'bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center font-bold' : ($day['isCurrentMonth'] ? 'text-gray-800 font-medium' : 'text-gray-400') }}">
                                {{ $day['date']->format('d') }}
                            </span>
                            @if($day['appointments']->isNotEmpty())
                                <span class="text-[10px] text-gray-400">{{ $day['appointments']->count() }}</span>
                            @endif
                        </div>

                        @foreach($day['appointments']->take(3) as $apt)
                            <a href="{{ route('appointments.show', $apt) }}"
                               class="block mb-1 px-1.5 py-0.5 rounded text-[11px] truncate {{ $statusColors[$apt->status] ?? 'bg-gray-100' }} hover:opacity-80">
                                <span class="font-semibold">{{ $apt->scheduled_at->format('H:i') }}</span>
                                {{ $apt->patient->last_name }}
                            </a>
                        @endforeach

                        @if($day['appointments']->count() > 3)
                            <a href="{{ route('appointments.index', ['view' => 'week', 'date' => $key]) }}"
                               class="block text-[10px] text-blue-600 hover:underline px-1.5">
                                +{{ $day['appointments']->count() - 3 }} mas
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Legend --}}
    <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-600">
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-200"></span> Programado</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-200"></span> Confirmado</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-200"></span> En espera</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-purple-200"></span> En consulta</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-200"></span> Completado</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-200"></span> Cancelado</span>
    </div>
</x-layouts.tenant>
