{{--
    Encabezado del expediente del paciente + barra de pestañas.
    Espera: $patient
    Las pestañas se muestran según permisos / módulos / especialidad.
--}}
@php
    $branding = \App\Models\InstallationSetting::current();
    $u = auth()->user();
    $showRecetas = $branding->moduleEnabled('prescriptions') && $u->can('prescriptions.view');
    $showConsultas = $u->can('consultations.view');
    $showCrecimiento = $u->specialty === 'pediatrics' && $patient->date_of_birth && $patient->gender;

    $tabBase = 'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors';
    $tabActive = 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400';
    $tabIdle = 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300';
@endphp

<div class="mb-6">
    <a href="{{ route('patients.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a pacientes</a>
</div>

{{-- Cabecera del paciente --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-4">
    <div class="flex justify-between items-start">
        <div class="flex items-start gap-4">
            {{-- Foto --}}
            <div class="flex-shrink-0">
                @if($patient->photo_url)
                    <img src="{{ $patient->photo_url }}" alt="{{ $patient->full_name }}"
                         class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700">
                @else
                    <div class="w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center border-2 border-gray-200 dark:border-gray-700">
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}</span>
                    </div>
                @endif
                <div class="mt-2 text-center">
                    <button type="button" onclick="document.getElementById('photo-input').click()"
                            class="text-xs text-blue-600 hover:underline">
                        {{ $patient->photo_url ? 'Cambiar' : 'Agregar foto' }}
                    </button>
                    @if($patient->photo_url)
                        <form action="{{ route('patients.photo.delete', $patient) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline ml-1">Quitar</button>
                        </form>
                    @endif
                </div>
                <form id="photo-form" action="{{ route('patients.photo', $patient) }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp"
                           onchange="document.getElementById('photo-form').submit()">
                </form>
            </div>

            {{-- Identidad --}}
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $patient->full_name }}</h2>
                <p class="text-gray-500 dark:text-gray-400">Expediente: {{ $patient->medical_record_number }}</p>
                <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <span>{{ $patient->age }} años</span>
                    <span>{{ $patient->gender == 'male' ? 'Masculino' : ($patient->gender == 'female' ? 'Femenino' : 'Otro') }}</span>
                    @if($patient->blood_type)
                        <span>Sangre: {{ $patient->blood_type }}</span>
                    @endif
                    @if($patient->phone)
                        <span>Tel: {{ $patient->phone }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('patients.edit', $patient) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Editar</a>
            <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">+ Nuevo turno</a>
        </div>
    </div>
</div>

{{-- Barra de pestañas --}}
<div class="border-b border-gray-200 dark:border-gray-700 mb-6">
    <nav class="flex gap-2">
        <a href="{{ route('patients.show', $patient) }}"
           class="{{ $tabBase }} {{ request()->routeIs('patients.show') ? $tabActive : $tabIdle }}">Resumen</a>

        @if($showConsultas)
        <a href="{{ route('patients.consultations', $patient) }}"
           class="{{ $tabBase }} {{ request()->routeIs('patients.consultations') ? $tabActive : $tabIdle }}">Consultas</a>
        @endif

        @if($showRecetas)
        <a href="{{ route('patients.prescriptions', $patient) }}"
           class="{{ $tabBase }} {{ request()->routeIs('patients.prescriptions') ? $tabActive : $tabIdle }}">Recetas</a>
        @endif

        @if($showCrecimiento)
        <a href="{{ route('patients.growth', $patient) }}"
           class="{{ $tabBase }} {{ request()->routeIs('patients.growth') ? $tabActive : $tabIdle }}">Crecimiento</a>
        @endif
    </nav>
</div>
