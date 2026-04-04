<x-layouts.tenant :title="'Gastos'">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Gastos</h2>
            <p class="text-gray-500 text-sm">Control de gastos del consultorio.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('expenses.summary') }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 text-sm font-medium">
                Ver resumen
            </a>
            <a href="{{ route('expenses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">
                + Nuevo gasto
            </a>
        </div>
    </div>

    {{-- Month filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('expenses.index') }}" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Mes:</label>
            <input type="month" name="month" value="{{ $month }}"
                   class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                   onchange="this.form.submit()">
        </form>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase">Total gastos del mes</p>
            <p class="text-2xl font-mono font-bold text-red-700">${{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase mb-2">Por categoria</p>
            @forelse($byCategory as $catName => $catTotal)
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">{{ $catName }}</span>
                    <span class="font-mono text-gray-800">${{ number_format($catTotal, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Sin gastos este mes.</p>
            @endforelse
        </div>
    </div>

    {{-- Expenses table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($expenses->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="mb-4">No hay gastos registrados este mes.</p>
                <a href="{{ route('expenses.create') }}" class="text-blue-600 hover:underline">Registrar el primer gasto</a>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($expenses as $expense)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700">
                                {{ $expense->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $expense->concept }}</td>
                        <td class="px-6 py-4 text-sm text-right font-mono font-semibold text-red-700">${{ number_format($expense->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($expense->is_recurring)
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">Recurrente</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500">Unico</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('expenses.edit', $expense) }}" class="text-blue-600 hover:underline">Editar</a>
                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Eliminar este gasto?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.tenant>
