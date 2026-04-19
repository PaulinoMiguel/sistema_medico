<x-layouts.tenant :title="'Detalle de Caja'">
    <div class="mb-6">
        <a href="{{ route('cash-registers.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a caja</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Detalle de Caja</h2>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase">Apertura</p>
            <p class="text-lg font-mono font-bold text-gray-800">${{ number_format($cashRegister->opening_amount, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $cashRegister->opened_at->format('d/m/Y H:i') }}</p>
            <p class="text-xs text-gray-400">{{ $cashRegister->openedBy->name }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase">Cobros</p>
            <p class="text-lg font-mono font-bold text-green-700">${{ number_format($cashRegister->total_collected, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $cashRegister->payments->count() }} cobro(s)</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase">Total esperado</p>
            <p class="text-lg font-mono font-bold text-gray-800">${{ number_format($cashRegister->opening_amount + $cashRegister->total_collected, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500 uppercase">Monto contado</p>
            @if($cashRegister->closing_amount !== null)
                @php $diff = $cashRegister->closing_amount - $cashRegister->expected_amount; @endphp
                <p class="text-lg font-mono font-bold {{ $diff == 0 ? 'text-green-700' : 'text-red-700' }}">
                    ${{ number_format($cashRegister->closing_amount, 2) }}
                </p>
                @if($diff != 0)
                    <p class="text-xs {{ $diff > 0 ? 'text-yellow-600' : 'text-red-600' }}">
                        Diferencia: {{ $diff > 0 ? '+' : '' }}${{ number_format($diff, 2) }}
                    </p>
                @else
                    <p class="text-xs text-green-600">Cuadra exacto</p>
                @endif
            @else
                <p class="text-lg font-mono font-bold text-yellow-600">Pendiente</p>
            @endif
        </div>
    </div>

    @if($cashRegister->closing_notes)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-xs text-yellow-600 uppercase mb-1">Notas del cierre</p>
            <p class="text-sm text-yellow-800">{{ $cashRegister->closing_notes }}</p>
        </div>
    @endif

    {{-- Payments List --}}
    @php
        $allPayments = $cashRegister->payments;
        $doctorsList = $allPayments->pluck('doctor')->filter()->unique('id')->sortBy('name');
    @endphp
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-semibold text-gray-800">Cobros registrados</h3>
                @if($doctorsList->count() > 1)
                    <select id="doctor-filter" onchange="filterByDoctor(this.value)"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los doctores</option>
                        @foreach($doctorsList as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
        @if($allPayments->isEmpty())
            <div class="p-8 text-center text-gray-500">
                No se registraron cobros en esta caja.
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recibo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medico</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cobro por</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($allPayments as $payment)
                    <tr class="hover:bg-gray-50 payment-row" data-doctor-id="{{ $payment->doctor_id }}">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->created_at->format('H:i') }}</td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ $payment->receipt_number }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payment->patient->full_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->doctor?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->concept }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->receivedBy->name }}</td>
                        <td class="px-6 py-4 text-sm text-right font-mono font-semibold text-gray-900 payment-amount" data-amount="{{ $payment->amount }}">${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="6" class="px-6 py-3 text-sm font-bold text-gray-800 text-right" id="total-label">Total cobrado:</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-lg text-green-700" id="total-amount">${{ number_format($cashRegister->total_collected, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

        @endif
    </div>

    <script>
        function filterByDoctor(doctorId) {
            const rows = document.querySelectorAll('.payment-row');
            let total = 0;
            rows.forEach(row => {
                const match = !doctorId || row.dataset.doctorId === doctorId;
                row.style.display = match ? '' : 'none';
                if (match) total += parseFloat(row.querySelector('.payment-amount').dataset.amount);
            });
            document.getElementById('total-label').textContent = doctorId ? 'Total filtrado:' : 'Total cobrado:';
            document.getElementById('total-amount').textContent = '$' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    </script>
</x-layouts.tenant>
