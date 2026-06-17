<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen clínico - {{ $consultation->patient->full_name }}</title>
    <style>
        @page { size: letter; margin: 1.5cm 2cm; }
        * { box-sizing: border-box; }
        body { font-family: Georgia, 'Times New Roman', serif; font-size: 12pt; color: #111; margin: 0; line-height: 1.4; }
        .header {
            border-bottom: 2px solid #111; padding-bottom: 8px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 16px;
        }
        .header .logo { max-width: 160px; max-height: 110px; flex-shrink: 0; }
        .header .info { flex: 1; }
        .doctor-name { font-size: 16pt; font-weight: bold; margin: 0; }
        .doctor-info { font-size: 10pt; color: #444; margin: 2px 0; }
        .clinic-info { font-size: 9pt; color: #555; margin-top: 4px; }
        .extra-header { font-size: 10pt; color: #555; font-style: italic; margin: 2px 0; white-space: pre-line; }
        .doc-title { font-size: 15pt; font-weight: bold; text-align: center; text-transform: uppercase; letter-spacing: 1px; margin: 8px 0 16px; }
        .patient-block {
            margin: 0 0 18px; padding: 8px 12px; border: 1px solid #999; border-radius: 4px; font-size: 11pt;
        }
        .patient-block .row { display: flex; justify-content: space-between; gap: 16px; }
        .patient-block .row + .row { margin-top: 4px; }
        .patient-block .field { flex: 1; }
        .patient-block label { font-weight: bold; color: #333; }
        .section { margin-bottom: 14px; }
        .section h3 { font-size: 11pt; margin: 0 0 4px; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
        .section p { margin: 0; white-space: pre-line; }
        table.codes { width: 100%; border-collapse: collapse; font-size: 11pt; }
        table.codes th, table.codes td { border: 1px solid #999; padding: 4px 8px; text-align: left; }
        table.codes th { background: #f0f0f0; font-size: 10pt; text-transform: uppercase; }
        .footer { margin-top: 48px; text-align: center; font-size: 11pt; }
        .footer .signature { display: inline-block; min-width: 60%; border-top: 1px solid #111; padding-top: 4px; }
        @media screen {
            body { background: #f3f4f6; padding: 24px; }
            .page { background: white; padding: 2cm; margin: 0 auto; max-width: 21cm; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            .print-bar {
                position: sticky; top: 0; background: #1f2937; color: white; padding: 12px 16px;
                margin: -24px -24px 24px; display: flex; justify-content: space-between; align-items: center;
            }
            .print-bar button { background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; }
        }
        @media print { .print-bar { display: none !important; } }
    </style>
</head>
<body>
    @php
        $cs = $consultation->clinical_summary ?? [];
        $doctor = $consultation->doctor;
        $patient = $consultation->patient;
        $address = $doctor->print_address ?? ($consultation->clinic?->address);
        $genderLabel = $patient->gender === 'male' ? 'Masculino' : ($patient->gender === 'female' ? 'Femenino' : 'Otro');
        $insuranceName = $cs['insurer_name'] ?? $patient->insurance_provider;
        $dtypeLabel = ($cs['diagnosis_type'] ?? '') === 'presuntivo' ? 'Presuntivo' : (($cs['diagnosis_type'] ?? '') === 'definitivo' ? 'Definitivo' : null);
        $procedures = $cs['procedures'] ?? [];
    @endphp

    <div class="print-bar">
        <span>Vista previa — Resumen clínico</span>
        <button onclick="window.print()">Imprimir</button>
    </div>

    <div class="page">
        {{-- Cabecera del doctor --}}
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
                @if($doctor->phone)
                    <p class="doctor-info">{{ $doctor->phone }}</p>
                @endif
                @if($address)
                    <p class="clinic-info">{{ $address }}</p>
                @endif
            </div>
        </div>

        <div class="doc-title">Resumen clínico</div>

        {{-- Datos del paciente --}}
        <div class="patient-block">
            <div class="row">
                <div class="field"><label>Paciente:</label> {{ $patient->full_name }}</div>
                <div><label>Fecha:</label> {{ $consultation->consultation_date->format('d/m/Y') }}</div>
            </div>
            <div class="row">
                @if($patient->document_number)
                    <div><label>Cédula/Afiliado:</label> {{ $patient->document_number }}</div>
                @endif
                <div><label>Edad:</label> {{ $patient->age }} años</div>
                <div><label>Sexo:</label> {{ $genderLabel }}</div>
            </div>
            @if($insuranceName)
            <div class="row">
                <div class="field"><label>Aseguradora:</label> {{ $insuranceName }}</div>
                @if($patient->insurance_policy_number)
                    <div><label>No. póliza:</label> {{ $patient->insurance_policy_number }}</div>
                @endif
            </div>
            @endif
        </div>

        {{-- Historia breve --}}
        @if(!empty($cs['summary']))
        <div class="section">
            <h3>Historia breve de la enfermedad actual</h3>
            <p>{{ $cs['summary'] }}</p>
        </div>
        @endif

        {{-- Diagnóstico --}}
        @if(!empty($cs['diagnosis']))
        <div class="section">
            <h3>Diagnóstico @if($dtypeLabel)({{ $dtypeLabel }})@endif</h3>
            <p>{{ $cs['diagnosis'] }}</p>
        </div>
        @endif

        {{-- Estudios realizados --}}
        @if(!empty($cs['studies_done']))
        <div class="section">
            <h3>Estudios realizados</h3>
            <p>{{ $cs['studies_done'] }}</p>
        </div>
        @endif

        {{-- Tratamientos previos --}}
        @if(!empty($cs['previous_treatments']))
        <div class="section">
            <h3>Tratamientos previos</h3>
            <p>{{ $cs['previous_treatments'] }}</p>
        </div>
        @endif

        {{-- Procedimiento / Estudio solicitado --}}
        @if(count($procedures))
        <div class="section">
            <h3>Procedimiento / Estudio solicitado</h3>
            <table class="codes">
                <thead>
                    <tr>
                        <th style="width:75%">Procedimiento</th>
                        <th>Código</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($procedures as $p)
                    <tr>
                        <td>{{ $p['name'] ?? '' }}</td>
                        <td>{{ $p['code'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            <div class="signature">Nombre, Firma y Código del Médico Tratante</div>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>
