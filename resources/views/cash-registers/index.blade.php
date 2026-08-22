<x-layouts.tenant :title="'Caja'">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Caja</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Apertura, cobros y cierre de caja diario.</p>
        </div>
    </div>

    {{-- Cajas contadas que esperan que el doctor reciba el dinero. Se muestran
         arriba porque es lo que hay que resolver, y no impiden abrir la del dia. --}}
    @foreach($pendingRegisters as $pendiente)
        @php
            $diferencia = $pendiente->difference;
            $soyDoctor = auth()->user()->isDoctor();
        @endphp
        <div class="bg-amber-50 border border-amber-300 rounded-lg p-6 mb-6">
            <div class="flex items-start justify-between gap-6 flex-wrap">
                <div>
                    <h3 class="text-lg font-semibold text-amber-900">Caja pendiente de recibir</h3>
                    <p class="text-sm text-amber-800 mt-1">
                        Cerrada por {{ $pendiente->closedBy->name ?? '—' }}
                        el {{ $pendiente->closed_at?->format('d/m/Y H:i') }}.
                        Falta que el doctor reciba el dinero.
                    </p>
                    <div class="flex gap-6 mt-3 text-sm">
                        <div>
                            <p class="text-xs text-amber-700">Esperado</p>
                            <p class="font-mono font-bold text-amber-900">${{ number_format($pendiente->expected_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-amber-700">Contado</p>
                            <p class="font-mono font-bold text-amber-900">${{ number_format($pendiente->closing_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-amber-700">Diferencia</p>
                            <p class="font-mono font-bold {{ $diferencia == 0 ? 'text-green-700' : 'text-red-700' }}">
                                {{ $diferencia > 0 ? '+' : '' }}${{ number_format($diferencia, 2) }}
                            </p>
                        </div>
                    </div>
                    @if($diferencia != 0)
                        <p class="mt-2 text-sm text-red-700">
                            El conteo no cuadra. Revisa antes de recibir, o devuelve la caja para recontar.
                        </p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('cash-registers.show', $pendiente) }}"
                       class="px-3 py-2 border border-amber-400 text-amber-800 rounded-md hover:bg-amber-100 text-sm">
                        Ver detalle
                    </a>
                    <button type="button" onclick="document.getElementById('aprobar-{{ $pendiente->id }}').classList.remove('hidden')"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                        Recibido conforme
                    </button>
                </div>
            </div>
        </div>

        {{-- Formulario de recepcion. Si lo abre el propio doctor no se le pide
             PIN: ya esta autenticado como el. Si lo abre la secretaria, el
             doctor teclea su PIN aqui mismo. --}}
        <div id="aprobar-{{ $pendiente->id }}" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Recibido conforme</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Se recibe ${{ number_format($pendiente->closing_amount, 2) }} en efectivo.
                </p>
                <form method="POST" action="{{ route('cash-registers.approve', $pendiente) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Doctor que recibe *</label>
                        <select name="doctor_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($clinicDoctors as $doc)
                                <option value="{{ $doc->id }}" @selected($soyDoctor && $doc->id === auth()->id())>{{ $doc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @unless($soyDoctor)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PIN de autorización *</label>
                            <input type="password" name="pin" required inputmode="numeric" autocomplete="off"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="••••">
                            <p class="mt-1 text-xs text-gray-500">Lo teclea el doctor. No es su contraseña de acceso.</p>
                        </div>
                    @endunless
                    @error('pin') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nota {{ $diferencia != 0 ? '*' : '(opcional)' }}
                        </label>
                        <input type="text" name="approval_notes" maxlength="500"
                               value="{{ old('approval_notes') }}"
                               placeholder="{{ $diferencia != 0 ? 'Explica la diferencia' : 'Observaciones' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('aprobar-{{ $pendiente->id }}').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                            Confirmar recepción
                        </button>
                    </div>
                </form>

                @if($soyDoctor)
                    <form method="POST" action="{{ route('cash-registers.reject', $pendiente) }}" class="mt-4 pt-4 border-t">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700 mb-1">¿No cuadra? Devolver para recontar</label>
                        <div class="flex gap-2">
                            <input type="text" name="approval_notes" maxlength="500" required
                                   placeholder="Motivo de la devolución"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <button type="submit" onclick="return confirm('La caja volverá a estar abierta y se borrará el conteo. Continuar?')"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium whitespace-nowrap">
                                Devolver
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endforeach

    {{-- Open/Close Register Card --}}
    @if($openRegister)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse mr-2"></span>
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-300">Caja abierta</h3>
                    </div>
                    <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                        Abierta por {{ $openRegister->openedBy->name }} el {{ $openRegister->opened_at->format('d/m/Y H:i') }}
                    </p>
                    <div class="flex gap-6 mt-3">
                        <div>
                            <p class="text-xs text-green-600 dark:text-green-400">Monto inicial</p>
                            <p class="text-lg font-mono font-bold text-green-800 dark:text-green-300">${{ number_format($openRegister->opening_amount, 2) }}</p>
                        </div>
                        {{-- Los dos subtotales suman el total cobrado. Se separan
                             con una linea para que se lea como un desglose. --}}
                        <div class="border-l border-green-300 pl-6">
                            <p class="text-xs text-green-600 dark:text-green-400">Subtotal efectivo</p>
                            <p class="text-lg font-mono text-green-800 dark:text-green-300">${{ number_format($openRegister->total_cash, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-green-600 dark:text-green-400">Subtotal transferencia</p>
                            <p class="text-lg font-mono text-green-800 dark:text-green-300">${{ number_format($openRegister->total_transfer, 2) }}</p>
                        </div>
                        <div class="border-t-2 border-green-400 pt-1 self-end">
                            <p class="text-xs font-semibold text-green-700 dark:text-green-300">Total cobrado</p>
                            <p class="text-xl font-mono font-bold text-green-900 dark:text-green-200">${{ number_format($openRegister->total_collected, 2) }}</p>
                        </div>
                        @if($openRegister->total_petty_expenses > 0)
                            <div>
                                <p class="text-xs text-red-600 dark:text-red-400">Gastos menores</p>
                                <p class="text-lg font-mono text-red-700 dark:text-red-400">−${{ number_format($openRegister->total_petty_expenses, 2) }}</p>
                            </div>
                        @endif
                        <div class="border-l border-green-300 pl-6">
                            {{-- Solo el efectivo, y descontando lo que salio de la
                                 gaveta: las transferencias nunca entraron a la caja
                                 y los gastos menores ya salieron de ella. --}}
                            <p class="text-xs text-green-600 dark:text-green-400">Esperado en caja</p>
                            <p class="text-lg font-mono font-bold text-green-800 dark:text-green-300">${{ number_format($openRegister->expected_cash, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payments detail (inline) --}}
        @php
            $allPayments = $openRegister->payments;
            $doctorsList = $allPayments->pluck('doctor')->filter()->unique('id')->sortBy('name');
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Cobros del día</h3>
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
                {{-- Solo si ademas puede operar esta caja: un doctor no cobra en
                     la caja de la secretaria, y mostrarle el boton solo lo
                     llevaria a un error. --}}
                @if(auth()->user()->can('payments.create') && $openRegister->isOperatedBy(auth()->user()))
                    <a href="{{ route('payments.create') }}" class="bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 text-sm font-medium">
                        + Registrar cobro
                    </a>
                @elseif(auth()->user()->can('payments.create'))
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-right max-w-xs">
                        Esta caja la abrió {{ $openRegister->openedBy->name }}.
                        Para cobrar por caja, abre la tuya.
                    </span>
                @endif
            </div>
            @if($allPayments->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    No se han registrado cobros en esta caja.
                </div>
            @else
                <table class="w-full" id="payments-table">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Recibo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Turno</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Médico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Concepto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cobro por</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Forma</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($allPayments as $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 payment-row" data-doctor-id="{{ $payment->doctor_id }}" data-method="{{ $payment->payment_method }}">
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->created_at->format('H:i') }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $payment->receipt_number }}</td>
                            <td class="px-6 py-4 text-sm font-mono">
                                {{-- Se usa el id directo y no la relacion: un turno oculto
                                     por el filtro de visibilidad dejaria la celda vacia. --}}
                                @if($payment->appointment_id)
                                    <a href="{{ route('appointments.show', $payment->appointment_id) }}"
                                       class="text-blue-600 hover:underline">#{{ $payment->appointment_id }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $payment->patient->full_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->doctor?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->concept }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->receivedBy->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-0.5 rounded text-xs {{ $payment->isTransfer() ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $payment->method_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-mono font-semibold text-gray-900 dark:text-gray-100 payment-amount" data-amount="{{ $payment->amount }}">${{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <td colspan="8" class="px-6 py-1 pt-3 text-sm text-gray-600 dark:text-gray-300 text-right">Subtotal efectivo:</td>
                            <td class="px-6 py-1 pt-3 text-right font-mono text-gray-700 dark:text-gray-300" id="subtotal-cash">${{ number_format($openRegister->total_cash, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="8" class="px-6 py-1 text-sm text-gray-600 dark:text-gray-300 text-right">Subtotal transferencia:</td>
                            <td class="px-6 py-1 text-right font-mono text-gray-700 dark:text-gray-300" id="subtotal-transfer">${{ number_format($openRegister->total_transfer, 2) }}</td>
                        </tr>
                        <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                            <td colspan="8" class="px-6 py-3 text-sm font-bold text-gray-800 dark:text-gray-200 text-right" id="total-label">Total cobrado:</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-lg text-green-700 dark:text-green-400" id="total-amount">${{ number_format($openRegister->total_collected, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

            @endif
        </div>

        {{-- Gastos menores: dinero que sale de la gaveta durante el dia --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Gastos menores</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Dinero que sale de la caja: comida, material gastable, mandados.
                    </p>
                </div>
                @can('expenses.petty-create')
                    <button type="button" onclick="document.getElementById('petty-modal').classList.remove('hidden')"
                            class="bg-red-600 text-white px-3 py-1.5 rounded-md hover:bg-red-700 text-sm font-medium">
                        + Registrar gasto
                    </button>
                @endcan
            </div>

            @if($openRegister->pettyExpenses->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    No se han registrado gastos en esta caja.
                </div>
            @else
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Concepto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Registrado por</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Recibo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($openRegister->pettyExpenses as $gasto)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $gasto->created_at->format('H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $gasto->concept }}
                                    @if($gasto->notes)
                                        <span class="block text-xs text-gray-500">{{ $gasto->notes }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $gasto->registeredBy->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($gasto->receipt_url)
                                        <a href="{{ $gasto->receipt_url }}" target="_blank" class="text-blue-600 hover:underline text-sm">Ver</a>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-mono font-semibold text-red-700 dark:text-red-400">−${{ number_format($gasto->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-sm font-bold text-gray-800 dark:text-gray-200 text-right">Total gastos:</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-lg text-red-700 dark:text-red-400">−${{ number_format($openRegister->total_petty_expenses, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>

        @can('expenses.petty-create')
        {{-- Formulario de gasto menor --}}
        {{-- Si la validacion rechazo el gasto, el modal se abre solo para que
             no se pierda lo escrito ni el mensaje de error. --}}
        <div id="petty-modal" class="{{ $errors->hasAny(['concept', 'amount', 'receipt']) ? '' : 'hidden' }} fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Registrar gasto menor</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Efectivo disponible en caja:
                    <strong class="text-gray-800 font-mono">${{ number_format($openRegister->available_cash, 2) }}</strong>
                </p>
                <form method="POST" action="{{ route('cash-registers.petty-expense') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">En qué se gastó *</label>
                        <input type="text" name="concept" required maxlength="255" value="{{ old('concept') }}"
                               placeholder="Ej: Almuerzo del personal"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('concept') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="amount" required step="0.01" min="0.01"
                                   max="{{ $openRegister->available_cash }}" value="{{ old('amount') }}"
                                   class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00">
                        </div>
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recibo (opcional)</label>
                        <input type="file" name="receipt" accept="image/*"
                               class="block w-full text-sm border border-gray-300 rounded-md file:bg-blue-50 file:border-0 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700">
                        @error('receipt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nota (opcional)</label>
                        <input type="text" name="notes" maxlength="500" value="{{ old('notes') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('petty-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium">
                            Registrar gasto
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endcan

        <script>
            const money = n => '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            function filterByDoctor(doctorId) {
                const rows = document.querySelectorAll('.payment-row');
                // Los subtotales se recalculan junto al total: si no, al filtrar
                // por doctor el desglose quedaria mostrando el dia completo.
                let total = 0, cash = 0, transfer = 0;
                rows.forEach(row => {
                    const match = !doctorId || row.dataset.doctorId === doctorId;
                    row.style.display = match ? '' : 'none';
                    if (!match) return;
                    const monto = parseFloat(row.querySelector('.payment-amount').dataset.amount);
                    total += monto;
                    if (row.dataset.method === 'transfer') { transfer += monto; } else { cash += monto; }
                });
                document.getElementById('total-label').textContent = doctorId ? 'Total filtrado:' : 'Total cobrado:';
                document.getElementById('total-amount').textContent = money(total);
                document.getElementById('subtotal-cash').textContent = money(cash);
                document.getElementById('subtotal-transfer').textContent = money(transfer);
            }
        </script>

        {{-- Lo gobierna el permiso: quien lleva la caja la cierra, sea la
             secretaria o la doctora el día que no hay secretaria. --}}
        @can('cash-register.close')
        <div class="flex justify-end mb-6">
            <button onclick="document.getElementById('close-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                Cerrar caja
            </button>
        </div>
        @endcan

        {{-- Close Modal --}}
        <div id="close-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cerrar Caja</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Total esperado en caja: <strong class="text-gray-800">${{ number_format($openRegister->expected_cash, 2) }}</strong>
                    <br>
                    <span class="text-xs">
                        Apertura ${{ number_format($openRegister->opening_amount, 2) }}
                        + efectivo ${{ number_format($openRegister->total_cash, 2) }}
                        @if($openRegister->total_petty_expenses > 0)
                            − gastos ${{ number_format($openRegister->total_petty_expenses, 2) }}
                        @endif.
                        @if($openRegister->total_transfer > 0)
                            No incluye ${{ number_format($openRegister->total_transfer, 2) }} en transferencias.
                        @endif
                    </span>
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
                    @can('cash-register.open')
                        <p class="text-sm text-yellow-700">Abre la caja para comenzar a registrar cobros del día.</p>
                    @else
                        <p class="text-sm text-yellow-700">No hay caja abierta en este momento.</p>
                    @endcan
                </div>
                @can('cash-register.open')
                <button onclick="document.getElementById('open-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                    Abrir caja
                </button>
                @endcan
            </div>
        </div>

        @can('cash-register.open')
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
        @endcan
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
