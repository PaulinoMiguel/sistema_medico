<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ordenes - {{ $consultation->patient->full_name }}</title>
    <style>
        @page {
            size: letter;
            margin: 1.5cm 2cm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 12pt;
            color: #111;
            margin: 0;
            line-height: 1.4;
        }
        .order-page {
            page-break-after: always;
            min-height: 24cm;
            display: flex;
            flex-direction: column;
        }
        .order-page:last-child { page-break-after: auto; }
        .header {
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .header .logo {
            max-width: 100px;
            max-height: 80px;
            flex-shrink: 0;
        }
        .header .info {
            flex: 1;
        }
        .doctor-name {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
        }
        .doctor-info {
            font-size: 10pt;
            color: #444;
            margin: 2px 0;
        }
        .clinic-info {
            font-size: 9pt;
            color: #555;
            margin-top: 4px;
        }
        .extra-header {
            font-size: 10pt;
            color: #555;
            font-style: italic;
            margin: 2px 0;
            white-space: pre-line;
        }
        .patient-block {
            margin: 16px 0 24px;
            padding: 8px 12px;
            border: 1px solid #999;
            border-radius: 4px;
            font-size: 11pt;
        }
        .patient-block .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }
        .patient-block .field {
            flex: 1;
        }
        .patient-block label {
            font-weight: bold;
            color: #333;
        }
        .order-title {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 16px 0 12px;
            text-align: center;
        }
        .order-body {
            font-size: 12pt;
            flex: 1;
        }
        .order-body .item {
            padding: 4px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .order-body .item .check {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1.5px solid #111;
            flex-shrink: 0;
        }
        .order-body .freeline {
            padding: 4px 0;
        }
        .order-body .info {
            padding: 4px 0;
            font-weight: bold;
        }
        .footer {
            margin-top: 32px;
            text-align: center;
            font-size: 11pt;
        }
        .footer .signature {
            display: inline-block;
            min-width: 60%;
            border-top: 1px solid #111;
            padding-top: 4px;
        }
        @media screen {
            body { background: #f3f4f6; padding: 24px; }
            .order-page {
                background: white;
                padding: 2cm;
                margin: 0 auto 24px;
                max-width: 21cm;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                page-break-after: auto;
            }
            .print-bar {
                position: sticky;
                top: 0;
                background: #1f2937;
                color: white;
                padding: 12px 16px;
                margin: -24px -24px 24px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .print-bar button {
                background: #2563eb;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            }
            .print-bar button:hover { background: #1d4ed8; }
        }
        @media print {
            .print-bar { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="print-bar">
        <span>Vista previa de ordenes — {{ count($selected) }} {{ count($selected) === 1 ? 'pagina' : 'paginas' }}</span>
        <button onclick="window.print()">Imprimir</button>
    </div>

    @php
        $doctor = $consultation->doctor;
        $address = $doctor->print_address ?? ($consultation->clinic?->address);
    @endphp
    @foreach($selected as $tpl)
        <div class="order-page">
            <div class="header">
                @if($doctor->print_logo_path)
                    <img src="{{ asset('storage/' . $doctor->print_logo_path) }}" alt="Logo" class="logo">
                @endif
                <div class="info">
                    <p class="doctor-name">{{ $doctor->name }}</p>
                    @if($doctor->professional_license)
                        <p class="doctor-info">Exequatur: {{ $doctor->professional_license }}</p>
                    @endif
                    @if($doctor->print_extra_header)
                        <p class="extra-header">{{ $doctor->print_extra_header }}</p>
                    @endif
                    <p class="doctor-info">
                        @if($doctor->phone){{ $doctor->phone }}@endif
                        @if($doctor->phone && $doctor->email) | @endif
                        @if($doctor->email){{ $doctor->email }}@endif
                        @if(($doctor->phone || $doctor->email) && $doctor->print_website) | @endif
                        @if($doctor->print_website){{ $doctor->print_website }}@endif
                    </p>
                    @if($address)
                        <p class="clinic-info">{{ $address }}</p>
                    @endif
                </div>
            </div>

            <div class="patient-block">
                <div class="row">
                    <div class="field"><label>Paciente:</label> {{ $consultation->patient->full_name }}</div>
                    <div><label>Fecha:</label> {{ $consultation->consultation_date->format('d/m/Y') }}</div>
                </div>
                <div class="row" style="margin-top: 4px;">
                    <div><label>Edad:</label> {{ $consultation->patient->age }} años</div>
                    @if($consultation->patient->document_number)
                        <div><label>Cedula:</label> {{ $consultation->patient->document_number }}</div>
                    @endif
                    <div><label>Sexo:</label> {{ $consultation->patient->gender === 'male' ? 'Masculino' : ($consultation->patient->gender === 'female' ? 'Femenino' : 'Otro') }}</div>
                </div>
            </div>

            <div class="order-title">{{ $tpl['label'] }}</div>

            <div class="order-body">
                @foreach($tpl['lines'] as $line)
                    @if($line === '')
                        <div class="freeline">&nbsp;</div>
                    @elseif(str_starts_with($line, '> '))
                        <div class="info">{{ substr($line, 2) }}</div>
                    @elseif(str_contains($line, '___'))
                        <div class="freeline">{{ $line }}</div>
                    @else
                        <div class="item"><span class="check"></span><span>{{ $line }}</span></div>
                    @endif
                @endforeach
            </div>

            <div class="footer">
                <div class="signature">Firma y sello</div>
            </div>
        </div>
    @endforeach

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 300);
        });
    </script>
</body>
</html>
