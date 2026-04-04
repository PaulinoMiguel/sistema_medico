<x-layouts.tenant :title="'Resumen Financiero'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Resumen Financiero</h2>
        <p class="text-gray-500 text-sm">Ingresos, gastos y balance de tu clinica.</p>
    </div>

    {{-- Month filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('expenses.summary') }}" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Periodo:</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                   onchange="this.form.submit()">
            <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</span>
        </form>
    </div>

    {{-- Main cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {{-- Income --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Ingresos</p>
                <div class="bg-green-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </div>
            </div>
            <p class="text-2xl font-mono font-bold text-green-700">${{ number_format($totalIncome, 2) }}</p>
            @if($prevIncome > 0)
                @php $incDiff = $totalIncome - $prevIncome; $incPct = ($incDiff / $prevIncome) * 100; @endphp
                <p class="text-xs mt-1 {{ $incDiff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $incDiff >= 0 ? '+' : '' }}{{ number_format($incPct, 1) }}% vs mes anterior
                </p>
            @endif
        </div>

        {{-- Expenses --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Gastos</p>
                <div class="bg-red-100 rounded-full p-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </div>
            </div>
            <p class="text-2xl font-mono font-bold text-red-700">${{ number_format($totalExpenses, 2) }}</p>
            @if($prevExpenses > 0)
                @php $expDiff = $totalExpenses - $prevExpenses; $expPct = ($expDiff / $prevExpenses) * 100; @endphp
                <p class="text-xs mt-1 {{ $expDiff <= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $expDiff >= 0 ? '+' : '' }}{{ number_format($expPct, 1) }}% vs mes anterior
                </p>
            @endif
        </div>

        {{-- Balance --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Balance (Utilidad)</p>
                <div class="{{ $balance >= 0 ? 'bg-blue-100' : 'bg-yellow-100' }} rounded-full p-2">
                    <svg class="w-5 h-5 {{ $balance >= 0 ? 'text-blue-600' : 'text-yellow-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-mono font-bold {{ $balance >= 0 ? 'text-blue-700' : 'text-yellow-700' }}">
                {{ $balance < 0 ? '-' : '' }}${{ number_format(abs($balance), 2) }}
            </p>
            @if($prevBalance != 0)
                @php $balDiff = $balance - $prevBalance; @endphp
                <p class="text-xs mt-1 {{ $balDiff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $balDiff >= 0 ? '+' : '' }}${{ number_format($balDiff, 2) }} vs mes anterior
                </p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Expenses by category --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Gastos por categoria</h3>
            @if($expensesByCategory->isEmpty())
                <p class="text-gray-400 text-sm">Sin gastos este mes.</p>
            @else
                <div class="space-y-3">
                    @php $maxCat = $expensesByCategory->max(); @endphp
                    @foreach($expensesByCategory as $catName => $catTotal)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700">{{ $catName }}</span>
                                <span class="font-mono font-semibold text-gray-800">${{ number_format($catTotal, 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-red-500 h-2.5 rounded-full" style="width: {{ ($catTotal / $maxCat) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between">
                    <span class="text-sm font-semibold text-gray-700">Total</span>
                    <span class="font-mono font-bold text-red-700">${{ number_format($totalExpenses, 2) }}</span>
                </div>
            @endif
        </div>

        {{-- Income vs Expenses visual --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ingresos vs Gastos</h3>
            <div class="space-y-4">
                {{-- Income bar --}}
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">Ingresos (cobros)</span>
                        <span class="font-mono font-semibold text-green-700">${{ number_format($totalIncome, 2) }}</span>
                    </div>
                    @php $maxVal = max($totalIncome, $totalExpenses, 1); @endphp
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-green-500 h-4 rounded-full" style="width: {{ ($totalIncome / $maxVal) * 100 }}%"></div>
                    </div>
                </div>

                {{-- Expenses bar --}}
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">Gastos</span>
                        <span class="font-mono font-semibold text-red-700">${{ number_format($totalExpenses, 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-red-500 h-4 rounded-full" style="width: {{ ($totalExpenses / $maxVal) * 100 }}%"></div>
                    </div>
                </div>

                {{-- Balance --}}
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700">Balance</span>
                        <span class="text-xl font-mono font-bold {{ $balance >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ $balance < 0 ? '-' : '+' }}${{ number_format(abs($balance), 2) }}
                        </span>
                    </div>
                    @if($totalIncome > 0)
                        <p class="text-xs text-gray-500 mt-1">
                            Margen: {{ number_format(($balance / $totalIncome) * 100, 1) }}%
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('expenses.index', ['month' => $month]) }}" class="text-blue-600 hover:underline text-sm">Ver detalle de gastos</a>
        <a href="{{ route('payments.index') }}" class="text-blue-600 hover:underline text-sm">Ver cobros</a>
    </div>
</x-layouts.tenant>
