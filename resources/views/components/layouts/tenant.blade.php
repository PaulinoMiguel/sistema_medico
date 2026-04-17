<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $branding = \App\Models\InstallationSetting::current(); @endphp
    <title>{{ $title ?? $branding->brand_name }} - {{ $branding->brand_name }}</title>
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root { --brand-primary: {{ $branding->primary_color }}; }
        /* Hide native disclosure marker on <details> for sidebar groups */
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] > summary .chevron { transform: rotate(180deg); }
        .chevron { transition: transform 0.15s ease; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 fixed h-full z-10 transition-colors">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                @if($branding->logoUrl())
                    <div class="flex items-center gap-2 mb-1">
                        <img src="{{ $branding->logoUrl() }}" alt="logo" class="h-8">
                        <h1 class="text-xl font-bold" style="color: var(--brand-primary)">{{ $branding->brand_name }}</h1>
                    </div>
                @else
                    <h1 class="text-xl font-bold" style="color: var(--brand-primary)">{{ $branding->brand_name }}</h1>
                @endif
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $branding->brand_tagline }}</p>
            </div>

            {{-- Clinic indicator / selector --}}
            @php
                $userClinics = auth()->user()->clinics;
                $activeClinic = $userClinics->firstWhere('id', session('active_clinic_id'));
            @endphp
            @if($userClinics->isNotEmpty())
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    @if($userClinics->count() > 1)
                        <form action="{{ route('clinic.select') }}" method="POST">
                            @csrf
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Clinica activa</label>
                            <select name="clinic_id" onchange="this.form.submit()"
                                    class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @foreach($userClinics as $clinic)
                                    <option value="{{ $clinic->id }}" {{ session('active_clinic_id') == $clinic->id ? 'selected' : '' }}>
                                        {{ $clinic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <div class="flex items-center">
                            <div class="bg-blue-100 dark:bg-blue-900/50 rounded-full p-1.5 mr-2">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Clinica</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $activeClinic?->name ?? $userClinics->first()->name }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <nav class="p-4 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 180px);">
                @php
                    $u = auth()->user();
                    $hasClinic = $userClinics->isNotEmpty();
                    $activeClass = 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                    $inactiveClass = 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700';
                    $groupSummaryClass = 'flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md cursor-pointer text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700';
                    $childItemClass = 'flex items-center px-3 py-2 pl-10 text-sm rounded-md';

                    // Module toggles (installation-level)
                    $modExpenses = $branding->moduleEnabled('expenses');
                    $modCashRegister = $branding->moduleEnabled('cash_register');
                    $modPrescriptions = $branding->moduleEnabled('prescriptions');
                    $modServices = $branding->moduleEnabled('services');

                    // Per-permission visibility flags (driven by spatie permissions)
                    $canConsultations = $u->can('consultations.view') || $u->can('consultations.create');
                    $canPrescriptions = $modPrescriptions && ($u->can('prescriptions.view') || $u->can('prescriptions.create'));
                    $showClinical = $canConsultations || $canPrescriptions;

                    $canPayments = $u->can('payments.view');
                    $canPaymentsCreate = $u->can('payments.create');
                    $canServices = $modServices && ($u->can('services.manage') || $u->can('services.view'));
                    $showIngresos = $canPayments || ($canPaymentsCreate && $u->isDoctor()) || $canServices;

                    $canExpenses = $modExpenses && $u->can('expenses.view');
                    $canExpenseCategories = $modExpenses && $u->can('expense-categories.manage');
                    $canExpenseSummary = $modExpenses && $u->can('expenses.view-summary');
                    $canMySummary = $modExpenses && $u->can('expenses.view-my-summary');
                    $canSharedPool = $modExpenses && $u->can('expenses.view-shared-pool');
                    $showEgresos = $canExpenses || $canExpenseCategories || $canExpenseSummary || $canMySummary || $canSharedPool;

                    $canStaffManage = $u->can('staff.manage');
                    $canRolesManage = $u->can('roles.manage');
                    $canSettings = $u->can('settings.manage');
                    $showAdmin = $canStaffManage || $canRolesManage || $canSettings;

                    $canCashRegister = $modCashRegister && $u->can('cash-register.view');

                    // Detect which group should be auto-expanded based on current route
                    $clinicalOpen = request()->routeIs('consultations.*') || request()->routeIs('prescriptions.*');
                    $ingresosOpen = request()->routeIs('payments.*') || request()->routeIs('services.*');
                    $isDirectChannel = request()->query('channel') === 'doctor_direct';
                    $egresosOpen = request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*');
                    $adminOpen = request()->routeIs('secretaries.*') || request()->routeIs('roles.*');
                @endphp

                {{-- Always visible --}}
                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Inicio
                </a>

                {{-- Operational items — hidden until at least one clinic exists.
                     This prevents the user from accidentally navigating to features
                     that require a clinic context before having one. --}}
                @if($hasClinic)
                @can('patients.view')
                <a href="{{ route('patients.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('patients.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Pacientes
                </a>
                @endcan
                @can('appointments.view')
                <a href="{{ route('appointments.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('appointments.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Turnos
                </a>
                @endcan

                {{-- Clinico --}}
                @if($showClinical)
                    <details class="mt-2" {{ $clinicalOpen ? 'open' : '' }}>
                        <summary class="{{ $groupSummaryClass }}">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Clinico
                            </span>
                            <svg class="chevron w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </summary>
                        <div class="mt-1 space-y-1">
                            @if($canConsultations)
                            <a href="{{ route('consultations.index') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('consultations.*') ? $activeClass : $inactiveClass }}">
                                Consultas
                            </a>
                            @endif
                            @if($canPrescriptions)
                            <a href="{{ route('prescriptions.index') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('prescriptions.*') ? $activeClass : $inactiveClass }}">
                                Recetas
                            </a>
                            @endif
                        </div>
                    </details>
                @endif

                {{-- Corte de Caja (top-level: operacion diaria de cierre) --}}
                @if($canCashRegister)
                <a href="{{ route('cash-registers.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('cash-registers.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Caja
                </a>
                @endif

                {{-- Ingresos --}}
                @if($showIngresos)
                <details class="mt-2" {{ $ingresosOpen ? 'open' : '' }}>
                    <summary class="{{ $groupSummaryClass }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-7 7m7-7l7 7"/></svg>
                            Ingresos
                        </span>
                        <svg class="chevron w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="mt-1 space-y-1">
                        @if($u->isDoctor() && $canPaymentsCreate)
                        <a href="{{ route('payments.index', ['channel' => 'doctor_direct']) }}"
                           class="{{ $childItemClass }} {{ request()->routeIs('payments.*') && $isDirectChannel ? $activeClass : $inactiveClass }}">
                            Mis cobros
                        </a>
                        @endif
                        @if($canPayments)
                        <a href="{{ route('payments.index') }}"
                           class="{{ $childItemClass }} {{ request()->routeIs('payments.*') && !$isDirectChannel ? $activeClass : $inactiveClass }}">
                            Cobros
                        </a>
                        @endif
                        @if($canServices)
                        <a href="{{ route('services.index') }}"
                           class="{{ $childItemClass }} {{ request()->routeIs('services.*') ? $activeClass : $inactiveClass }}">
                            Servicios
                        </a>
                        @endif
                    </div>
                </details>
                @endif

                {{-- Egresos --}}
                @if($showEgresos)
                    <details class="mt-2" {{ $egresosOpen ? 'open' : '' }}>
                        <summary class="{{ $groupSummaryClass }}">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-3 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m0 0l-7-7m7 7l7-7"/></svg>
                                Egresos
                            </span>
                            <svg class="chevron w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </summary>
                        <div class="mt-1 space-y-1">
                            @if($canExpenses)
                            <a href="{{ route('expenses.index') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('expenses.index') || request()->routeIs('expenses.create') || request()->routeIs('expenses.show') || request()->routeIs('expenses.edit') ? $activeClass : $inactiveClass }}">
                                Gastos
                            </a>
                            @endif
                            @if($canExpenseCategories)
                            <a href="{{ route('expense-categories.index') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('expense-categories.*') ? $activeClass : $inactiveClass }}">
                                Categorias
                            </a>
                            @endif
                            @if($canMySummary)
                            <a href="{{ route('expenses.my-summary') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('expenses.my-summary') ? $activeClass : $inactiveClass }}">
                                Mi resumen
                            </a>
                            @endif
                            @if($canSharedPool)
                            <a href="{{ route('expenses.shared-pool') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('expenses.shared-pool') ? $activeClass : $inactiveClass }}">
                                Gastos compartidos
                            </a>
                            @endif
                            @if($canExpenseSummary)
                            <a href="{{ route('expenses.summary') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('expenses.summary') ? $activeClass : $inactiveClass }}">
                                Resumen clinica
                            </a>
                            @endif
                        </div>
                    </details>
                @endif

                @endif {{-- end hasClinic --}}

                @if($showAdmin)
                    <details class="mt-2" {{ $adminOpen ? 'open' : '' }}>
                        <summary class="{{ $groupSummaryClass }}">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Administracion
                            </span>
                            <svg class="chevron w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </summary>
                        <div class="mt-1 space-y-1">
                            @if($canStaffManage)
                            <a href="{{ route('secretaries.index') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('secretaries.*') ? $activeClass : $inactiveClass }}">
                                Secretarias
                            </a>
                            @endif
                            @if($canRolesManage)
                            <a href="{{ route('roles.index') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('roles.*') ? $activeClass : $inactiveClass }}">
                                Roles y permisos
                            </a>
                            @endif
                            @if($canSettings)
                            <a href="{{ route('settings.edit') }}"
                               class="{{ $childItemClass }} {{ request()->routeIs('settings.*') ? $activeClass : $inactiveClass }}">
                                Configuracion
                            </a>
                            @endif
                        </div>
                    </details>
                @endif

                <a href="{{ route('profile.edit') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md mt-2 {{ request()->routeIs('profile.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Mi Perfil
                </a>
                <div class="pb-6"></div>
            </nav>

            {{-- User info at bottom --}}
            <div class="absolute bottom-0 w-full p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.edit') }}" class="flex items-center min-w-0 group">
                        @if(auth()->user()->profile_photo_url)
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                        @else
                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            </div>
                        @endif
                        <div class="ml-2 min-w-0">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->role_label }}</p>
                        </div>
                    </a>
                    <div class="flex items-center gap-2">
                        {{-- Theme toggle --}}
                        <button onclick="toggleTheme()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Cambiar tema">
                            {{-- Sun (visible in dark) --}}
                            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            {{-- Moon (visible in light) --}}
                            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>
                        {{-- Logout --}}
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Cerrar sesion">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="ml-64 flex-1 p-8">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>

    @livewireScripts
</body>
</html>
