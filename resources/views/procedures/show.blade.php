<x-layouts.tenant :title="'Códigos - ' . $procedure->name">
    <div class="mb-6">
        <a href="{{ route('procedures.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a procedimientos</a>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $procedure->name }}</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Código, simón y monto que cada aseguradora asigna a este procedimiento. Deja vacía una aseguradora para no enlazarla.</p>
    </div>

    @if($insurers->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center text-gray-500 dark:text-gray-400">
            No hay aseguradoras activas. Crea aseguradoras primero en <a href="{{ route('insurers.index') }}" class="text-blue-600 hover:underline">Aseguradoras</a>.
        </div>
    @else
        <form method="POST" action="{{ route('procedures.matrix', $procedure) }}">
            @csrf @method('PUT')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aseguradora</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Código</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Simón</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($insurers as $insurer)
                        @php $p = $pivots[$insurer->id] ?? ['code' => null, 'simon' => null, 'monto' => null]; @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $insurer->name }}</td>
                            <td class="px-4 py-3">
                                <input type="text" name="rows[{{ $insurer->id }}][code]" value="{{ $p['code'] }}"
                                       class="w-32 px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="rows[{{ $insurer->id }}][simon]" value="{{ $p['simon'] }}"
                                       class="w-28 px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="rows[{{ $insurer->id }}][monto]" value="{{ $p['monto'] !== null ? rtrim(rtrim(number_format($p['monto'], 2, '.', ''), '0'), '.') : '' }}"
                                       inputmode="decimal" placeholder="0.00"
                                       class="w-28 px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md text-sm">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                    Guardar códigos
                </button>
            </div>
        </form>
    @endif
</x-layouts.tenant>
