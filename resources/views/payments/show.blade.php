<x-layouts.tenant :title="'Recibo de Pago'">
    <div class="mb-6">
        <a href="{{ route('payments.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a cobros</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8" id="receipt">
            {{-- Header --}}
            <div class="text-center border-b border-gray-200 pb-6 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Recibo de Pago</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $payment->receipt_number }}</p>
            </div>

            {{-- Details --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Fecha</p>
                    <p class="text-sm font-medium text-gray-800">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Recibio</p>
                    <p class="text-sm font-medium text-gray-800">{{ $payment->receivedBy->name }}</p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-xs text-gray-500 uppercase mb-1">Paciente</p>
                <p class="text-lg font-semibold text-gray-800">{{ $payment->patient->full_name }}</p>
            </div>

            <div class="border border-gray-200 rounded-lg overflow-hidden mb-6">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ $payment->concept }}
                                @if($payment->service)
                                    <span class="text-xs text-gray-500 block">Servicio: {{ $payment->service->name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-mono">${{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="px-4 py-3 text-sm font-bold text-gray-800">Total pagado</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-lg text-green-700">${{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($payment->notes)
                <div class="mb-6">
                    <p class="text-xs text-gray-500 uppercase mb-1">Notas</p>
                    <p class="text-sm text-gray-600">{{ $payment->notes }}</p>
                </div>
            @endif

            <div class="text-center text-xs text-gray-400 border-t border-gray-200 pt-4">
                <p>Pago en efectivo</p>
            </div>
        </div>

        <div class="mt-4 flex justify-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900 text-sm font-medium">
                Imprimir recibo
            </button>
            <a href="{{ route('payments.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                Nuevo cobro
            </a>
        </div>
    </div>

    <style>
        @media print {
            aside, .mb-6 > a, .mt-4, nav { display: none !important; }
            main { margin-left: 0 !important; padding: 0 !important; }
            #receipt { box-shadow: none; }
        }
    </style>
</x-layouts.tenant>
