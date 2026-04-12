<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        // Scope filters automatically: doctor sees own, secretary sees
        // services from doctors in her clinics. Order by name.
        $services = Service::orderBy('name')->get();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
        ]);

        Service::create([
            ...$validated,
            'doctor_id' => $request->user()->id,
        ]);

        return redirect()->route('services.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Inline service creation from the payment form.
     * Returns JSON so the frontend can append the new option to the dropdown
     * without losing the in-progress payment data.
     */
    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
        ]);

        $service = Service::create([
            ...$validated,
            'doctor_id' => $request->user()->id,
        ]);

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'doctor_id' => $service->doctor_id,
        ], 201);
    }

    public function edit(Service $service)
    {
        // Only the owning doctor can edit her own services.
        abort_if($service->doctor_id !== auth()->id(), 403);

        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        abort_if($service->doctor_id !== auth()->id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
        ]);

        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    public function toggle(Service $service)
    {
        abort_if($service->doctor_id !== auth()->id(), 403);

        $service->update(['is_active' => ! $service->is_active]);

        $status = $service->is_active ? 'activado' : 'desactivado';

        return redirect()->route('services.index')
            ->with('success', "Servicio {$status}.");
    }
}
