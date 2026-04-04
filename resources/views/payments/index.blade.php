<x-layouts.tenant :title="'Cobros'">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Cobros</h2>
            <p class="text-gray-500 text-sm">Registro de pagos recibidos.</p>
        </div>
        <a href="{{ route('payments.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium">
            + Nuevo cobro
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($payments->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p class="mb-4">No hay cobros registrados.</p>
                <a href="{{ route('payments.create') }}" class="text-blue-600 hover:underline">Registrar el primer cobro</a>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recibo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cobro por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ $payment->receipt_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payment->patient->full_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->concept }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right font-mono font-semibold">${{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->receivedBy->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('payments.show', $payment) }}" class="text-blue-600 hover:underline">Ver recibo</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-3 border-t border-gray-200">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</x-layouts.tenant>
