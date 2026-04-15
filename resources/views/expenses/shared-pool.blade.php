<x-layouts.tenant :title="'Gastos compartidos'">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Gastos compartidos</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm">
                Pool de egresos sin dueño asignado &mdash; se reparte entre los doctores de la clinica.
            </p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Metodo de split</p>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                {{ ['equal' => 'Partes iguales', 'percentage' => 'Porcentajes', 'by_income' => 'Por ingresos'][$split_method] ?? $split_method }}
            </p>
        </div>
    </div>

    {{-- Month filter --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('expenses.shared-pool') }}" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Periodo:</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm text-sm"
                   onchange="this.form.submit()">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
            </span>
        </form>
    </div>

    {{-- Total card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 mb-6">
        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total del pool</p>
        <p class="text-3xl font-mono font-bold text-red-700 dark:text-red-400 mt-1">
            ${{ number_format($total, 2) }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Por doctor --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">Reparto por doctor</h3>
            @if($by_doctor->isEmpty())
                <p class="text-sm text-gray-500">No hay doctores asociados a esta clinica.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="text-left py-2">Doctor</th>
                            <th class="text-right py-2">%</th>
                            <th class="text-right py-2">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($by_doctor as $row)
                            <tr>
                                <td class="py-2 text-gray-800 dark:text-gray-200">{{ $row['doctor']->name }}</td>
                                <td class="py-2 text-right font-mono text-gray-600 dark:text-gray-400">
                                    {{ number_format($row['percentage'] * 100, 1) }}%
                                </td>
                                <td class="py-2 text-right font-mono text-red-700 dark:text-red-400">
                                    ${{ number_format($row['amount'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Por categoria --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">Por categoria</h3>
            @if($by_category->isEmpty())
                <p class="text-sm text-gray-500">Sin gastos compartidos en el periodo.</p>
            @else
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($by_category as $name => $amount)
                            <tr>
                                <td class="py-2 text-gray-800 dark:text-gray-200">{{ $name ?? 'Sin categoria' }}</td>
                                <td class="py-2 text-right font-mono text-red-700 dark:text-red-400">
                                    ${{ number_format($amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Detalle --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 mt-6">
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-3">Detalle</h3>
        @if($expenses->isEmpty())
            <p class="text-sm text-gray-500">Sin gastos compartidos en el periodo.</p>
        @else
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500 uppercase border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="text-left py-2">Fecha</th>
                        <th class="text-left py-2">Categoria</th>
                        <th class="text-left py-2">Concepto</th>
                        <th class="text-left py-2">Registrado por</th>
                        <th class="text-right py-2">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($expenses as $e)
                        <tr>
                            <td class="py-2 text-gray-700 dark:text-gray-300">{{ $e->expense_date->format('d/m/Y') }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300">{{ $e->category?->name ?? '-' }}</td>
                            <td class="py-2 text-gray-800 dark:text-gray-200">{{ $e->concept }}</td>
                            <td class="py-2 text-gray-500 dark:text-gray-400">{{ $e->registeredBy?->name ?? '-' }}</td>
                            <td class="py-2 text-right font-mono text-red-700 dark:text-red-400">
                                ${{ number_format($e->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.tenant>
