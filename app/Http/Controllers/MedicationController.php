<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    /** Vías de administración (value => label visible). */
    public const ROUTES = [
        'oral' => 'Oral',
        'sublingual' => 'Sublingual',
        'topical' => 'Tópica',
        'intramuscular' => 'Intramuscular',
        'intravenous' => 'Intravenosa',
        'rectal' => 'Rectal',
        'ophthalmic' => 'Oftálmica',
        'otic' => 'Ótica',
        'nasal' => 'Nasal',
        'inhaled' => 'Inhalada',
    ];

    public function index(Request $request)
    {
        $medications = Medication::where('doctor_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        $routeOptions = self::ROUTES;

        return view('medications.index', compact('medications', 'routeOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        Medication::create([
            ...$validated,
            'doctor_id' => $request->user()->id,
        ]);

        return redirect()->route('medications.index')
            ->with('success', 'Medicamento agregado al banco.');
    }

    /**
     * Carga masiva: una línea por medicamento, campos separados por "|":
     *   Nombre | Dosis | Duración | Vía | Observación
     * Solo el Nombre es obligatorio. La Vía acepta la etiqueta (Oral, Tópica…)
     * o la clave; por defecto 'oral'. Omite los nombres que ya existen.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'list' => 'required|string',
        ]);

        $doctorId = $request->user()->id;

        // Mapa para resolver la Vía desde su etiqueta o su clave.
        $routeLookup = [];
        foreach (self::ROUTES as $key => $label) {
            $routeLookup[$key] = $key;
            $routeLookup[mb_strtolower($label)] = $key;
        }

        // Nombres ya existentes (en minúsculas) para no duplicar.
        $existing = Medication::where('doctor_id', $doctorId)
            ->pluck('name')
            ->mapWithKeys(fn ($n) => [mb_strtolower($n) => true])
            ->all();

        $created = 0;
        $skipped = 0;

        foreach (preg_split('/\r\n|\r|\n/', $validated['list']) as $line) {
            $parts = array_map('trim', explode('|', trim($line)));
            $name = $parts[0] ?? '';
            if ($name === '') {
                continue;
            }

            $key = mb_strtolower($name);
            if (isset($existing[$key])) {
                $skipped++;
                continue;
            }

            $via = mb_strtolower($parts[3] ?? '');

            Medication::create([
                'doctor_id' => $doctorId,
                'name' => $name,
                'dosage' => ($parts[1] ?? '') !== '' ? $parts[1] : null,
                'duration' => ($parts[2] ?? '') !== '' ? $parts[2] : null,
                'route' => $routeLookup[$via] ?? 'oral',
                'instructions' => ($parts[4] ?? '') !== '' ? $parts[4] : null,
            ]);

            $existing[$key] = true;
            $created++;
        }

        return redirect()->route('medications.index')
            ->with('success', "Carga masiva completada: {$created} agregados" . ($skipped ? ", {$skipped} ya existían (omitidos)." : "."));
    }

    public function update(Request $request, Medication $medication)
    {
        abort_if($medication->doctor_id !== $request->user()->id, 403);

        $medication->update($this->validateData($request));

        return redirect()->route('medications.index')
            ->with('success', 'Medicamento actualizado.');
    }

    public function destroy(Request $request, Medication $medication)
    {
        abort_if($medication->doctor_id !== $request->user()->id, 403);

        $medication->delete();

        return redirect()->route('medications.index')
            ->with('success', 'Medicamento eliminado del banco.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'route' => 'required|string|in:' . implode(',', array_keys(self::ROUTES)),
            'instructions' => 'nullable|string|max:1000',
        ]);
    }
}
