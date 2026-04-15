<x-layouts.tenant :title="'Detalle del Turno'">
    @php
        $colors = [
            'scheduled'=>'bg-gray-100 text-gray-800',
            'confirmed'=>'bg-blue-100 text-blue-800',
            'in_waiting_room'=>'bg-yellow-100 text-yellow-800',
            'in_progress'=>'bg-purple-100 text-purple-800',
            'completed'=>'bg-green-100 text-green-800',
            'cancelled'=>'bg-red-100 text-red-800',
            'no_show'=>'bg-red-100 text-red-800',
        ];
        $labels = [
            'scheduled'=>'Programado',
            'confirmed'=>'Confirmado',
            'in_waiting_room'=>'En sala de espera',
            'in_progress'=>'En consulta',
            'completed'=>'Completado',
            'cancelled'=>'Cancelado',
            'no_show'=>'No asistio',
        ];
        $types = [
            'first_visit'=>'Primera vez',
            'follow_up'=>'Control',
            'pre_operative'=>'Pre-quirurgico',
            'post_operative'=>'Post-quirurgico',
            'urodynamic_study'=>'Urodinamia',
            'procedure'=>'Procedimiento',
            'emergency'=>'Urgencia',
            'surgical'=>'Cirugia',
        ];

        $_ = null; // actions handled inline below
    @endphp

    <div class="mb-6">
        <a href="{{ route('appointments.index', ['date' => $appointment->scheduled_at->toDateString()]) }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a turnos</a>
    </div>

    {{-- Status flow bar --}}
    @if(!in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex items-center justify-between">
                {{-- Progress steps --}}
                <div class="flex items-center gap-1 flex-1">
                    @php
                        $steps = ['scheduled', 'confirmed', 'in_waiting_room', 'in_progress', 'completed'];
                        $currentIndex = array_search($appointment->status, $steps);
                    @endphp
                    @foreach($steps as $i => $step)
                        <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold
                                {{ $i < $currentIndex ? 'bg-green-500 text-white' : ($i === $currentIndex ? 'bg-blue-600 text-white ring-4 ring-blue-100' : 'bg-gray-200 text-gray-500') }}">
                                @if($i < $currentIndex)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            @if($i < count($steps) - 1)
                                <div class="flex-1 h-1 mx-2 rounded {{ $i < $currentIndex ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-between mt-2 text-[10px] text-gray-500 px-1">
                <span>Programado</span>
                <span>Confirmado</span>
                <span>En espera</span>
                <span>En consulta</span>
                <span>Completado</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main info --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Turno #{{ $appointment->id }}</h2>
                        <p class="text-gray-500">{{ $appointment->scheduled_at->translatedFormat('l, d \\d\\e F \\d\\e Y - H:i') }} hs</p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $colors[$appointment->status] ?? '' }}">
                        {{ $labels[$appointment->status] ?? $appointment->status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Paciente</dt>
                            <dd><a href="{{ route('patients.show', $appointment->patient) }}" class="text-blue-600 hover:underline font-medium">{{ $appointment->patient->full_name }}</a></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Doctor</dt>
                            <dd>{{ $appointment->doctor->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Clinica</dt>
                            <dd>{{ $appointment->clinic->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Duracion</dt>
                            <dd>{{ $appointment->duration_minutes }} min</dd>
                        </div>
                    </dl>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Tipo</dt>
                            <dd>{{ $types[$appointment->type] ?? $appointment->type }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Motivo</dt>
                            <dd>{{ $appointment->reason ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Notas</dt>
                            <dd>{{ $appointment->notes ?? '-' }}</dd>
                        </div>
                        @if($appointment->cancellation_reason)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Motivo cancelacion</dt>
                                <dd class="text-red-600">{{ $appointment->cancellation_reason }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200 flex gap-3">
                    <a href="{{ route('appointments.edit', $appointment) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Editar</a>

                    @if(auth()->user()->isDoctor() && in_array($appointment->status, ['in_waiting_room', 'in_progress', 'completed']))
                        @php $existingConsultation = $appointment->consultation; @endphp
                        @if($existingConsultation)
                            <a href="{{ route($existingConsultation->isSigned() ? 'consultations.show' : 'consultations.edit', $existingConsultation) }}"
                               style="background-color:#9333ea;color:#fff;" class="px-4 py-2 rounded-md text-sm font-medium">
                                {{ $existingConsultation->isSigned() ? 'Ver consulta' : 'Continuar consulta' }}
                            </a>
                        @else
                            <form action="{{ route('consultations.from-appointment', $appointment) }}" method="POST">
                                @csrf
                                <button type="submit" style="background-color:#9333ea;color:#fff;" class="px-4 py-2 rounded-md text-sm font-medium">
                                    Iniciar consulta
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Action panel --}}
        <div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones</h3>

                @if($appointment->status === 'scheduled')
                    <form action="{{ route('appointments.status', $appointment) }}" method="POST" class="mb-6">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" style="background-color:#2563eb;color:#fff;" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Confirmar turno
                        </button>
                    </form>
                @elseif($appointment->status === 'confirmed')
                    <form action="{{ route('appointments.status', $appointment) }}" method="POST" class="mb-6">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="in_waiting_room">
                        <button type="submit" style="background-color:#eab308;color:#fff;" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Paciente llego
                        </button>
                    </form>
                @elseif($appointment->status === 'in_waiting_room')
                    @php
                        $isDoc = auth()->user()->isDoctor();
                        $existing = $appointment->consultation;
                    @endphp
                    @if($isDoc && !$existing)
                        {{-- El doctor crea la consulta (y de paso cambia el estado a in_progress). --}}
                        <form action="{{ route('consultations.from-appointment', $appointment) }}" method="POST" class="mb-6">
                            @csrf
                            <button type="submit" style="background-color:#9333ea;color:#fff;" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Iniciar consulta
                            </button>
                        </form>
                    @elseif($isDoc && $existing)
                        <a href="{{ route($existing->isSigned() ? 'consultations.show' : 'consultations.edit', $existing) }}"
                           style="background-color:#9333ea;color:#fff;" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition mb-6">
                            {{ $existing->isSigned() ? 'Ver consulta' : 'Continuar consulta' }}
                        </a>
                    @else
                        {{-- Secretaria: solo cambia el estado a in_progress. --}}
                        <form action="{{ route('appointments.status', $appointment) }}" method="POST" class="mb-6">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit" style="background-color:#9333ea;color:#fff;" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Pasar a consulta
                            </button>
                        </form>
                    @endif
                @elseif($appointment->status === 'in_progress')
                    @if(auth()->user()->isDoctor())
                    <form action="{{ route('appointments.status', $appointment) }}" method="POST" class="mb-6">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" style="background-color:#16a34a;color:#fff;" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Finalizar consulta
                        </button>
                    </form>
                    @else
                    <div class="text-center py-4">
                        <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-purple-700 font-medium">Consulta en curso</p>
                    </div>
                    @endif
                @endif

                @if($appointment->status === 'completed')
                    <div class="text-center py-4">
                        <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="text-green-700 font-medium">Consulta completada</p>
                    </div>
                @elseif($appointment->status === 'cancelled')
                    <div class="text-center py-4">
                        <div class="bg-red-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <p class="text-red-700 font-medium">Turno cancelado</p>
                    </div>
                @endif

                {{-- Secondary actions --}}
                @if($appointment->canBeCancelled())
                    <div class="border-t pt-4 mt-4">
                        <form action="{{ route('appointments.status', $appointment) }}" method="POST"
                              onsubmit="
                                  event.preventDefault();
                                  let reason = prompt('Motivo de cancelacion:');
                                  if(reason !== null) {
                                      this.querySelector('[name=cancellation_reason]').value = reason;
                                      this.submit();
                                  }
                              ">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <input type="hidden" name="cancellation_reason" value="">
                            <button type="submit" class="w-full px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 transition">
                                Cancelar turno
                            </button>
                        </form>

                        @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                            <form action="{{ route('appointments.status', $appointment) }}" method="POST" class="mt-2">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="no_show">
                                <button type="submit" class="w-full px-4 py-2 border border-gray-200 text-gray-500 rounded-lg text-sm hover:bg-gray-50 transition"
                                        onclick="return confirm('Marcar como que no asistio?')">
                                    No asistio
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.tenant>
