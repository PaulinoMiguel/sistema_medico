<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta {{ $prescription->prescription_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }

        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; color: #2563eb; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #666; }

        .patient-info { margin-bottom: 20px; padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
        .patient-info table { width: 100%; }
        .patient-info td { padding: 3px 8px; font-size: 11px; }
        .patient-info .label { font-weight: bold; color: #555; width: 120px; }

        .rx-symbol { font-size: 28px; font-weight: bold; color: #2563eb; margin: 15px 0 10px; }

        .medications { margin-bottom: 20px; }
        .medication { margin-bottom: 15px; padding: 10px; border-left: 3px solid #2563eb; background-color: #fafafa; }
        .medication .name { font-size: 14px; font-weight: bold; color: #1e3a5f; margin-bottom: 5px; }
        .medication .details { font-size: 11px; color: #555; }
        .medication .details span { margin-right: 15px; }
        .medication .instructions { font-size: 11px; color: #666; font-style: italic; margin-top: 4px; }

        .diagnosis { margin-bottom: 15px; padding: 8px 12px; background-color: #eff6ff; border-radius: 4px; }
        .diagnosis .label { font-weight: bold; font-size: 11px; color: #555; }

        .notes { margin-bottom: 20px; padding: 8px 12px; background-color: #fefce8; border-radius: 4px; font-size: 11px; }

        .footer { margin-top: 60px; }
        .signature-line { width: 250px; border-top: 1px solid #333; margin: 0 auto; text-align: center; padding-top: 5px; }
        .signature-line p { font-size: 11px; color: #555; }
        .footer-info { text-align: center; margin-top: 20px; font-size: 10px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $prescription->clinic->name }}</h1>
        <p>{{ $prescription->clinic->address ?? '' }}</p>
        <p>{{ $prescription->clinic->phone ?? '' }}</p>
    </div>

    <div class="patient-info">
        <table>
            <tr>
                <td class="label">Paciente:</td>
                <td>{{ $prescription->patient->full_name }}</td>
                <td class="label">Fecha:</td>
                <td>{{ $prescription->prescription_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Expediente:</td>
                <td>{{ $prescription->patient->medical_record_number }}</td>
                <td class="label">Receta No.:</td>
                <td>{{ $prescription->prescription_number }}</td>
            </tr>
            @if($prescription->patient->date_of_birth)
            <tr>
                <td class="label">Edad:</td>
                <td>{{ $prescription->patient->age }} años</td>
                <td></td>
                <td></td>
            </tr>
            @endif
        </table>
    </div>

    @if($prescription->diagnosis)
        <div class="diagnosis">
            <span class="label">Diagnostico:</span> {{ $prescription->diagnosis }}
        </div>
    @endif

    <div class="rx-symbol">Rx</div>

    <div class="medications">
        @foreach($prescription->items as $index => $item)
            <div class="medication">
                <div class="name">{{ $index + 1 }}. {{ $item->medication_name }}</div>
                <div class="details">
                    @php
                        $routeLabels = ['oral'=>'Oral','sublingual'=>'Sublingual','topical'=>'Topica','intramuscular'=>'Intramuscular','intravenous'=>'Intravenosa','rectal'=>'Rectal','ophthalmic'=>'Oftalmica','otic'=>'Otica','nasal'=>'Nasal','inhaled'=>'Inhalada'];
                    @endphp
                    <span><strong>Dosis:</strong> {{ $item->dosage }}</span>
                    <span><strong>Frecuencia:</strong> {{ $item->frequency }}</span>
                    <span><strong>Via:</strong> {{ $routeLabels[$item->route] ?? $item->route }}</span>
                    @if($item->duration)<span><strong>Duracion:</strong> {{ $item->duration }}</span>@endif
                    @if($item->quantity)<span><strong>Cantidad:</strong> {{ $item->quantity }}</span>@endif
                </div>
                @if($item->instructions)
                    <div class="instructions">{{ $item->instructions }}</div>
                @endif
            </div>
        @endforeach
    </div>

    @if($prescription->notes)
        <div class="notes">
            <strong>Notas:</strong> {{ $prescription->notes }}
        </div>
    @endif

    <div class="footer">
        <div class="signature-line">
            <p>Dr. {{ $prescription->doctor->name }}</p>
            <p>Medico Tratante</p>
        </div>
        <div class="footer-info">
            <p>{{ $prescription->clinic->name }} | Receta generada el {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
