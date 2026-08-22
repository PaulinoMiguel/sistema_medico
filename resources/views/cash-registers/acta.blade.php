<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de entrega de caja #{{ $cashRegister->id }}</title>
    <style>
        @page { size: letter; margin: 1.5cm 2cm; }
        * { box-sizing: border-box; }
        body { font-family: Georgia, 'Times New Roman', serif; font-size: 11pt; color: #111; margin: 0; line-height: 1.4; }

        .print-bar {
            background: #f3f4f6; border-bottom: 1px solid #d1d5db; padding: 10px 16px;
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .print-bar button {
            background: #2563eb; color: #fff; border: 0; padding: 8px 18px;
            border-radius: 4px; font-size: 11pt; cursor: pointer;
        }
        @media print { .print-bar { display: none !important; } }

        .titulo { font-size: 15pt; font-weight: bold; text-align: center; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px; }
        .subtitulo { text-align: center; font-size: 10pt; color: #555; margin-bottom: 18px; }

        .datos { border: 1px solid #999; border-radius: 4px; padding: 8px 12px; margin-bottom: 16px; font-size: 10.5pt; }
        .datos .fila { display: flex; justify-content: space-between; gap: 16px; }
        .datos .fila + .fila { margin-top: 3px; }

        h3 { font-size: 11pt; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #ccc;
             padding-bottom: 3px; margin: 16px 0 8px; }

        table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        th { text-align: left; border-bottom: 1px solid #999; padding: 4px 6px; font-size: 9.5pt; text-transform: uppercase; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }

        .cuadre { margin-top: 14px; margin-left: auto; width: 60%; font-size: 10.5pt; }
        .cuadre .linea { display: flex; justify-content: space-between; padding: 3px 0; }
        .cuadre .total { border-top: 2px solid #111; font-weight: bold; font-size: 11.5pt; padding-top: 5px; }
        .cuadre .resta { color: #991b1b; }

        .declaracion { margin-top: 22px; font-size: 10.5pt; text-align: justify; }

        .firmas { margin-top: 40px; display: flex; justify-content: space-between; gap: 40px; }
        .firma { width: 45%; text-align: center; }
        .firma img { max-height: 55px; max-width: 190px; margin-bottom: 2px; }
        .firma .linea { border-top: 1px solid #111; padding-top: 4px; font-size: 10pt; }
        .firma .rol { font-size: 9pt; color: #555; }

        .pie { margin-top: 26px; font-size: 8.5pt; color: #666; text-align: center; border-top: 1px solid #ddd; padding-top: 6px; }
    </style>
</head>
<body>
    @php
        $entrega = $cashRegister->closedBy;
        $recibe = $cashRegister->approvedBy;
        $diferencia = $cashRegister->difference;
    @endphp

    <div class="print-bar">
        <span>Acta de entrega de caja — {{ $cashRegister->clinic->name ?? '' }}</span>
        <button onclick="window.print()">Imprimir</button>
    </div>

    <p class="titulo">Acta de entrega de caja</p>
    <p class="subtitulo">{{ $cashRegister->clinic->name ?? '' }} · Caja N.º {{ $cashRegister->id }}</p>

    <div class="datos">
        <div class="fila">
            <div><strong>Apertura:</strong> {{ $cashRegister->opened_at->format('d/m/Y H:i') }} por {{ $cashRegister->openedBy->name ?? '—' }}</div>
            <div><strong>Cierre:</strong> {{ $cashRegister->closed_at?->format('d/m/Y H:i') ?? '—' }}</div>
        </div>
        <div class="fila">
            <div><strong>Entrega:</strong> {{ $entrega->name ?? '—' }}</div>
            <div><strong>Recibe:</strong> {{ $recibe->name ?? '—' }}</div>
        </div>
        <div class="fila">
            <div><strong>Recibido conforme el:</strong> {{ $cashRegister->approved_at?->format('d/m/Y H:i') ?? '—' }}</div>
            <div><strong>Cobros:</strong> {{ $cashRegister->payments->count() }}</div>
        </div>
    </div>

    <h3>Cobros del día</h3>
    @if($cashRegister->payments->isEmpty())
        <p style="font-size:10pt;color:#666;">No se registraron cobros.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Hora</th><th>Recibo</th><th>Paciente</th><th>Concepto</th><th>Forma</th><th class="num">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashRegister->payments as $cobro)
                    <tr>
                        <td>{{ $cobro->created_at->format('H:i') }}</td>
                        <td>{{ $cobro->receipt_number }}</td>
                        <td>{{ $cobro->patient?->full_name ?? '—' }}</td>
                        <td>{{ $cobro->concept }}</td>
                        <td>{{ $cobro->method_label }}</td>
                        <td class="num">{{ number_format($cobro->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($cashRegister->pettyExpenses->isNotEmpty())
        <h3>Gastos menores</h3>
        <table>
            <thead>
                <tr><th>Hora</th><th>Concepto</th><th>Registrado por</th><th class="num">Monto</th></tr>
            </thead>
            <tbody>
                @foreach($cashRegister->pettyExpenses as $gasto)
                    <tr>
                        <td>{{ $gasto->created_at->format('H:i') }}</td>
                        <td>{{ $gasto->concept }}</td>
                        <td>{{ $gasto->registeredBy->name ?? '—' }}</td>
                        <td class="num">−{{ number_format($gasto->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3>Cuadre</h3>
    <div class="cuadre">
        <div class="linea"><span>Monto de apertura</span><span class="num">{{ number_format($cashRegister->opening_amount, 2) }}</span></div>
        <div class="linea"><span>Subtotal efectivo</span><span class="num">{{ number_format($cashRegister->total_cash, 2) }}</span></div>
        <div class="linea"><span>Subtotal transferencia</span><span class="num">{{ number_format($cashRegister->total_transfer, 2) }}</span></div>
        @if($cashRegister->total_petty_expenses > 0)
            <div class="linea resta"><span>Gastos menores</span><span class="num">−{{ number_format($cashRegister->total_petty_expenses, 2) }}</span></div>
        @endif
        <div class="linea total"><span>Efectivo esperado en caja</span><span class="num">{{ number_format($cashRegister->expected_amount, 2) }}</span></div>
        <div class="linea"><span>Efectivo contado</span><span class="num">{{ number_format($cashRegister->closing_amount, 2) }}</span></div>
        @if($diferencia !== null && abs($diferencia) >= 0.01)
            <div class="linea resta"><span><strong>Diferencia</strong></span><span class="num"><strong>{{ $diferencia > 0 ? '+' : '' }}{{ number_format($diferencia, 2) }}</strong></span></div>
        @endif
    </div>

    @if($cashRegister->closing_notes)
        <p style="margin-top:12px;font-size:10pt;"><strong>Notas del cierre:</strong> {{ $cashRegister->closing_notes }}</p>
    @endif
    @if($cashRegister->approval_notes)
        <p style="margin-top:6px;font-size:10pt;"><strong>Notas de la recepción:</strong> {{ $cashRegister->approval_notes }}</p>
    @endif

    <p class="declaracion">
        Quien recibe declara haber contado y recibido conforme la cantidad de
        <strong>{{ number_format($cashRegister->closing_amount, 2) }}</strong> en efectivo,
        correspondiente a la caja del {{ $cashRegister->opened_at->format('d/m/Y') }}
        @if($diferencia !== null && abs($diferencia) >= 0.01)
            , dejando constancia de una diferencia de {{ number_format($diferencia, 2) }} respecto a lo esperado
        @endif
        . Las transferencias detalladas no forman parte del efectivo entregado.
    </p>

    <div class="firmas">
        <div class="firma">
            @if($entrega?->digital_signature_path)
                <img src="{{ $firma($entrega->digital_signature_path) }}" alt="">
            @endif
            <div class="linea">{{ $entrega->name ?? '' }}</div>
            <div class="rol">Entrega</div>
        </div>
        <div class="firma">
            @if($recibe?->digital_signature_path)
                <img src="{{ $firma($recibe->digital_signature_path) }}" alt="">
            @endif
            <div class="linea">{{ $recibe->name ?? '' }}</div>
            <div class="rol">Recibe conforme</div>
        </div>
    </div>

    <p class="pie">
        Documento generado por el sistema el {{ now()->format('d/m/Y H:i') }}.
        Refleja la caja tal como fue aprobada y no puede modificarse.
    </p>
</body>
</html>
