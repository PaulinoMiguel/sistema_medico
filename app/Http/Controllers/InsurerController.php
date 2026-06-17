<?php

namespace App\Http\Controllers;

use App\Models\Insurer;
use Illuminate\Http\Request;

/**
 * Catálogo UNIVERSAL de aseguradoras (ARS). Lo mantiene la secretaria (permiso
 * insurers.manage). Los códigos/montos por procedimiento se gestionan en
 * ProcedureController (tabla cruce procedure_insurer).
 */
class InsurerController extends Controller
{
    public function index()
    {
        $insurers = Insurer::withCount('procedures')->orderBy('name')->get();

        return view('insurers.index', compact('insurers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);

        Insurer::create($validated);

        return redirect()->route('insurers.index')->with('success', 'Aseguradora agregada.');
    }

    public function update(Request $request, Insurer $insurer)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);

        $insurer->update($validated);

        return redirect()->back()->with('success', 'Aseguradora actualizada.');
    }

    public function toggle(Insurer $insurer)
    {
        $insurer->update(['is_active' => ! $insurer->is_active]);

        return redirect()->back()->with('success', 'Aseguradora actualizada.');
    }

    public function destroy(Insurer $insurer)
    {
        $insurer->delete();

        return redirect()->route('insurers.index')->with('success', 'Aseguradora eliminada.');
    }
}
