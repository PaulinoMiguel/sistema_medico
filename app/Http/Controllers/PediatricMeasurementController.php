<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PediatricMeasurement;
use App\Services\GrowthService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PediatricMeasurementController extends Controller
{
    public function __construct(private GrowthService $growth)
    {
    }

    /**
     * Guarda una nueva medicion para un paciente. Calcula Z-scores y los cachea.
     */
    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'consultation_id' => 'nullable|exists:consultations,id',
            'measured_at' => 'required|date|before_or_equal:today',
            'weight_kg' => 'nullable|numeric|min:0.3|max:250',
            'height_cm' => 'nullable|numeric|min:30|max:220',
            'head_circumference_cm' => 'nullable|numeric|min:25|max:65',
            'notes' => 'nullable|string|max:500',
        ]);

        if (! $patient->date_of_birth) {
            abort(422, 'El paciente no tiene fecha de nacimiento registrada.');
        }

        $measuredAt = Carbon::parse($validated['measured_at']);
        $ageMonths = $patient->ageInMonthsAt($measuredAt);
        $sex = $this->mapSex($patient->gender);

        $correctedAge = $patient->isPreterm()
            ? $this->growth->correctedAgeMonths($patient->gestational_age_weeks, $ageMonths)
            : null;

        // Para calcular z-scores usamos la edad corregida si aplica.
        $zAge = $correctedAge ?? $ageMonths;

        $weight = $validated['weight_kg'] ?? null;
        $height = $validated['height_cm'] ?? null;
        $hc = $validated['head_circumference_cm'] ?? null;
        $bmi = ($weight && $height) ? round($weight / (($height / 100) ** 2), 2) : null;

        $measurement = PediatricMeasurement::create([
            'patient_id' => $patient->id,
            'consultation_id' => $validated['consultation_id'] ?? null,
            'recorded_by' => $request->user()->id,
            'measured_at' => $measuredAt,
            'age_months' => $ageMonths,
            'corrected_age_months' => $correctedAge,
            'weight_kg' => $weight,
            'height_cm' => $height,
            'head_circumference_cm' => $hc,
            'bmi' => $bmi,
            'weight_z' => $weight && $sex ? $this->growth->zScore('weight_for_age', $sex, $zAge, $weight) : null,
            'height_z' => $height && $sex ? $this->growth->zScore('height_for_age', $sex, $zAge, $height) : null,
            'head_circumference_z' => $hc && $sex ? $this->growth->zScore('head_circumference_for_age', $sex, $zAge, $hc) : null,
            'bmi_z' => $bmi && $sex ? $this->growth->zScore('bmi_for_age', $sex, $zAge, $bmi) : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Medicion registrada.');
    }

    /**
     * Vista de curvas de crecimiento del paciente: 4 graficos con curvas LMS
     * + puntos historicos. Solo si el paciente tiene fecha de nacimiento y sexo.
     */
    public function charts(Patient $patient)
    {
        abort_unless($patient->date_of_birth && in_array($patient->gender, ['male', 'female']), 422,
            'El paciente debe tener fecha de nacimiento y sexo registrados.');

        $sex = $this->mapSex($patient->gender);
        $measurements = $patient->pediatricMeasurements()->get();
        $isPreterm = $patient->isPreterm();

        // Rango etario del paciente para las curvas (hasta 18 anos = 216 meses).
        $maxAge = min(216, $patient->ageInMonthsAt(now()));
        $rangeEnd = max(6, ceil($maxAge * 1.1)); // un poco mas alla del paciente

        $curves = [
            'weight_for_age' => $this->growth->curveData('weight_for_age', $sex, 0, $rangeEnd),
            'height_for_age' => $this->growth->curveData('height_for_age', $sex, 0, $rangeEnd),
            'head_circumference_for_age' => $maxAge <= 36
                ? $this->growth->curveData('head_circumference_for_age', $sex, 0, min(36, $rangeEnd))
                : null,
            'bmi_for_age' => $maxAge >= 24
                ? $this->growth->curveData('bmi_for_age', $sex, 24, $rangeEnd)
                : null,
        ];

        return view('patients.growth', compact('patient', 'measurements', 'curves', 'sex', 'isPreterm'));
    }

    public function destroy(Patient $patient, PediatricMeasurement $measurement)
    {
        abort_unless($measurement->patient_id === $patient->id, 404);
        $measurement->delete();
        return back()->with('success', 'Medicion eliminada.');
    }

    /**
     * Endpoint AJAX: dado paciente + fecha + mediciones, devuelve Z-scores y
     * percentiles en tiempo real (antes de guardar).
     */
    public function calculate(Request $request, Patient $patient): JsonResponse
    {
        $data = $request->validate([
            'measured_at' => 'required|date',
            'weight_kg' => 'nullable|numeric',
            'height_cm' => 'nullable|numeric',
            'head_circumference_cm' => 'nullable|numeric',
        ]);

        if (! $patient->date_of_birth || ! $patient->gender) {
            return response()->json(['error' => 'Paciente sin fecha de nacimiento o sexo.'], 422);
        }

        $ageMonths = $patient->ageInMonthsAt(Carbon::parse($data['measured_at']));
        $sex = $this->mapSex($patient->gender);

        $correctedAge = $patient->isPreterm()
            ? $this->growth->correctedAgeMonths($patient->gestational_age_weeks, $ageMonths)
            : null;

        $zAge = $correctedAge ?? $ageMonths;

        $w = $data['weight_kg'] ?? null;
        $h = $data['height_cm'] ?? null;
        $hc = $data['head_circumference_cm'] ?? null;
        $bmi = ($w && $h) ? round($w / (($h / 100) ** 2), 2) : null;

        $out = [
            'age_months' => $ageMonths,
            'corrected_age_months' => $correctedAge,
            'is_preterm' => $patient->isPreterm(),
            'bmi' => $bmi,
            'indicators' => [],
        ];

        $map = [
            'weight' => ['weight_for_age', $w],
            'height' => ['height_for_age', $h],
            'head_circumference' => ['head_circumference_for_age', $hc],
            'bmi' => ['bmi_for_age', $bmi],
        ];
        foreach ($map as $key => [$indicator, $value]) {
            if ($value === null || !$sex) {
                $out['indicators'][$key] = null;
                continue;
            }
            $z = $this->growth->zScore($indicator, $sex, $zAge, (float) $value);
            $out['indicators'][$key] = $z !== null
                ? ['z' => $z, 'percentile' => $this->growth->percentile($z)]
                : null;
        }

        return response()->json($out);
    }

    private function mapSex(?string $gender): ?string
    {
        return match ($gender) {
            'male' => 'male',
            'female' => 'female',
            default => null,
        };
    }
}
