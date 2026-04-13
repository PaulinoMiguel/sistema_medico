@php $isDirect = ($channel ?? null) === 'doctor_direct'; @endphp
<x-layouts.tenant :title="$isDirect ? 'Mis Cobros' : 'Cobros'">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $isDirect ? 'Mis Cobros' : 'Cobros' }}</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm">
                {{ $isDirect ? 'Cobros personales (cirugias, procedimientos). No pasan por caja.' : 'Registro de pagos recibidos.' }}
            </p>
        </div>
        @if($isDirect)
        <a href="{{ route('payments.create', ['channel' => 'doctor_direct']) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium">
            + Nuevo cobro personal
        </a>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        @if($payments->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <p class="mb-4">{{ $isDirect ? 'No tienes cobros personales registrados.' : 'No hay cobros registrados.' }}</p>
                @if($isDirect)
                <a href="{{ route('payments.create', ['channel' => 'doctor_direct']) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                    Registrar el primer cobro
                </a>
                @endif
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Recibo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Concepto</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monto</th>
                        @if(!$isDirect)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cobro por</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($payments as $payment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $payment->receipt_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $payment->patient->full_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->concept }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 text-right font-mono font-semibold">${{ number_format($payment->amount, 2) }}</td>
                        @if(!$isDirect)
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->receivedBy->name }}</td>
                        @endif
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('payments.show', $payment) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Ver recibo</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <td colspan="{{ $isDirect ? 4 : 5 }}" class="px-6 py-3 text-sm font-bold text-gray-800 dark:text-gray-200 text-right">Total:</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-lg text-green-700 dark:text-green-400">${{ number_format($total, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $payments->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</x-layouts.tenant>
