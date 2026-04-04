<x-layouts.tenant :title="'Corte de Caja'">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Corte de Caja</h2>
            <p class="text-gray-500 text-sm">Apertura y cierre de caja diario.</p>
        </div>
    </div>

    {{-- Open/Close Register Card --}}
    @if($openRegister)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse mr-2"></span>
                        <h3 class="text-lg font-semibold text-green-800">Caja abierta</h3>
                    </div>
                    <p class="text-sm text-green-700 mt-1">
                        Abierta por {{ $openRegister->openedBy->name }} el {{ $openRegister->opened_at->format('d/m/Y H:i') }}
                    </p>
                    <div class="flex gap-6 mt-3">
                        <div>
                            <p class="text-xs text-green-600">Monto inicial</p>
                            <p class="text-lg font-mono font-bold text-green-800">${{ number_format($openRegister->opening_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-green-600">Cobros del dia</p>
                            <p class="text-lg font-mono font-bold text-green-800">${{ number_format($openRegister->total_collected, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-green-600">Total esperado</p>
                            <p class="text-lg font-mono font-bold text-green-800">${{ number_format($openRegister->opening_amount + $openRegister->total_collected, 2) }}</p>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <a href="{{ route('cash-registers.show', $openRegister) }}" class="text-green-700 hover:underline text-sm block mb-2">Ver detalle</a>
                    <button onclick="document.getElementById('close-modal').classList.remove('hidden')"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                        Cerrar caja
                    </button>
                </div>
            </div>
        </div>

        {{-- Close Modal --}}
        <div id="close-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cerrar Caja</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Total esperado en caja: <strong class="text-gray-800">${{ number_format($openRegister->opening_amount + $openRegister->total_collected, 2) }}</strong>
                </p>
                <form method="POST" action="{{ route('cash-registers.close', $openRegister) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto contado en caja *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="closing_amount" required step="0.01" min="0"
                                   class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                        <textarea name="closing_notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Observaciones del cierre (opcional)"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('close-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium">Cerrar caja</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-yellow-800">Caja cerrada</h3>
                    <p class="text-sm text-yellow-700">Abre la caja para comenzar a registrar cobros del dia.</p>
                </div>
                <button onclick="document.getElementById('open-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                    Abrir caja
                </button>
            </div>
        </div>

        {{-- Open Modal --}}
        <div id="open-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Abrir Caja</h3>
                <form method="POST" action="{{ route('cash-registers.open') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto inicial en caja *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="opening_amount" required step="0.01" min="0" value="0"
                                   class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('open-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">Abrir caja</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- History --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Historial de cajas</h3>
        </div>
        @if($registers->isEmpty())
            <div class="p-8 text-center text-gray-500">
                No hay registros de caja aun.
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Apertura</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cierre</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Inicial</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cobros</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Esperado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Contado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($registers as $register)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $register->opened_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $register->closed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-right font-mono">${{ number_format($register->opening_amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-mono">${{ number_format($register->total_collected, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-mono">${{ number_format($register->expected_amount ?? ($register->opening_amount + $register->total_collected), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-mono">
                            @if($register->closing_amount !== null)
                                @php $diff = $register->closing_amount - $register->expected_amount; @endphp
                                <span class="{{ $diff < 0 ? 'text-red-600' : ($diff > 0 ? 'text-yellow-600' : 'text-green-600') }}">
                                    ${{ number_format($register->closing_amount, 2) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $register->isOpen() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $register->isOpen() ? 'Abierta' : 'Cerrada' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('cash-registers.show', $register) }}" class="text-blue-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $registers->links() }}
            </div>
        @endif
    </div>
</x-layouts.tenant>
