<x-layouts.tenant :title="'Mi Resumen'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Mi Resumen Financiero</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Neto personal del doctor en esta clinica.</p>
    </div>

    {{-- Month filter --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('expenses.my-summary') }}" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Periodo:</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm text-sm"
                   onchange="this.form.submit()">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
            </span>
        </form>
    </div>

    {{-- Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Mis ingresos</p>
            <p class="text-2xl font-mono font-bold text-green-700 dark:text-green-400 mt-1">
                ${{ number_format($income, 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Cobros atribuidos a mi (doctor_id)</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Mis gastos propios</p>
            <p class="text-2xl font-mono font-bold text-red-700 dark:text-red-400 mt-1">
                ${{ number_format($own_expenses, 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Gastos marcados como personales mios</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Mi parte del pool</p>
            <p class="text-2xl font-mono font-bold text-orange-700 dark:text-orange-400 mt-1">
                ${{ number_format($shared_portion, 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                {{ number_format($shared_percentage * 100, 1) }}% de ${{ number_format($shared_total, 2) }} compartidos
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 {{ $net >= 0 ? 'ring-2 ring-blue-500' : 'ring-2 ring-yellow-500' }}">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Mi neto</p>
            <p class="text-2xl font-mono font-bold {{ $net >= 0 ? 'text-blue-700 dark:text-blue-400' : 'text-yellow-700 dark:text-yellow-400' }} mt-1">
                ${{ number_format($net, 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Ingresos - gastos propios - pool</p>
        </div>
    </div>

    {{-- Formula --}}
    <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-sm font-mono text-gray-700 dark:text-gray-300">
        ${{ number_format($income, 2) }}
        <span class="text-gray-400">(ingresos)</span>
        &minus;
        ${{ number_format($own_expenses, 2) }}
        <span class="text-gray-400">(propios)</span>
        &minus;
        ${{ number_format($shared_portion, 2) }}
        <span class="text-gray-400">(pool {{ number_format($shared_percentage * 100, 1) }}%)</span>
        =
        <span class="font-bold {{ $net >= 0 ? 'text-blue-700 dark:text-blue-400' : 'text-yellow-700 dark:text-yellow-400' }}">
            ${{ number_format($net, 2) }}
        </span>
    </div>

    @can('expenses.view-shared-pool')
        <div class="mt-4">
            <a href="{{ route('expenses.shared-pool', ['month' => $month]) }}"
               class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                Ver desglose de gastos compartidos &rarr;
            </a>
        </div>
    @endcan
</x-layouts.tenant>
