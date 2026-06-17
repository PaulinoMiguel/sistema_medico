<?php

namespace App\Http\Controllers;

use App\Models\Insurer;
use App\Models\Procedure;
use App\Services\ProcedureCatalogImporter;
use Illuminate\Http\Request;

/**
 * Catálogo de procedimientos y su código/simón/monto por aseguradora
 * (tabla cruce procedure_insurer). Universal; lo mantiene la secretaria.
 */
class ProcedureController extends Controller
{
    public function index()
    {
        $procedures = Procedure::withCount('insurers')->orderBy('name')->get();

        return view('procedures.index', compact('procedures'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);

        $procedure = Procedure::create($validated);

        return redirect()->route('procedures.show', $procedure)
            ->with('success', 'Procedimiento creado. Completa los códigos por aseguradora.');
    }

    public function update(Request $request, Procedure $procedure)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);

        $procedure->update($validated);

        return redirect()->back()->with('success', 'Procedimiento actualizado.');
    }

    public function toggle(Procedure $procedure)
    {
        $procedure->update(['is_active' => ! $procedure->is_active]);

        return redirect()->back()->with('success', 'Procedimiento actualizado.');
    }

    public function destroy(Procedure $procedure)
    {
        $procedure->delete();

        return redirect()->route('procedures.index')->with('success', 'Procedimiento eliminado.');
    }

    /**
     * Editor de la matriz: las 10 aseguradoras con su código/simón/monto para
     * este procedimiento.
     */
    public function show(Procedure $procedure)
    {
        $insurers = Insurer::where('is_active', true)->orderBy('name')->get();

        // Valores actuales del cruce, indexados por insurer_id.
        $pivots = $procedure->insurers()->get()
            ->keyBy('id')
            ->map(fn ($i) => [
                'code' => $i->pivot->code,
                'simon' => $i->pivot->simon,
                'monto' => $i->pivot->monto,
            ]);

        return view('procedures.show', compact('procedure', 'insurers', 'pivots'));
    }

    public function updateMatrix(Request $request, Procedure $procedure)
    {
        $rows = (array) $request->input('rows', []);

        $sync = [];
        foreach ($rows as $insurerId => $vals) {
            $code = trim((string) ($vals['code'] ?? '')) ?: null;
            $simon = trim((string) ($vals['simon'] ?? '')) ?: null;
            $montoRaw = preg_replace('/[^0-9.\-]/', '', (string) ($vals['monto'] ?? ''));
            $monto = ($montoRaw === '' || $montoRaw === '-') ? null : (float) $montoRaw;

            // Solo se enlaza una aseguradora si tiene al menos un dato.
            if ($code !== null || $simon !== null || $monto !== null) {
                $sync[(int) $insurerId] = ['code' => $code, 'simon' => $simon, 'monto' => $monto];
            }
        }

        // sync attach/actualiza las aseguradoras con datos y quita las vaciadas.
        $procedure->insurers()->sync($sync);

        return redirect()->route('procedures.show', $procedure)->with('success', 'Códigos guardados.');
    }

    public function import(Request $request, ProcedureCatalogImporter $importer)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $rows = [];
        if (($handle = fopen($request->file('csv')->getRealPath(), 'r')) !== false) {
            $first = true;
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                // Salta la fila de encabezado si parece serlo.
                if ($first) {
                    $first = false;
                    if (stripos((string) ($data[0] ?? ''), 'procedimiento') !== false) {
                        continue;
                    }
                }
                $rows[] = $data;
            }
            fclose($handle);
        }

        $result = $importer->import($rows);

        return redirect()->route('procedures.index')->with('success',
            "Importado: {$result['procedures']} procedimientos, {$result['insurers']} aseguradoras, {$result['links']} códigos"
            . ($result['skipped'] ? ", {$result['skipped']} filas omitidas." : '.'));
    }
}
