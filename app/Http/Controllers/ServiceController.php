<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $clinicId = session('active_clinic_id');

        $services = Service::where('clinic_id', $clinicId)
            ->orderBy('name')
            ->get();

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
            'clinic_id' => session('active_clinic_id'),
        ]);

        return redirect()->route('services.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    public function edit(Service $service)
    {
        abort_if($service->clinic_id != session('active_clinic_id'), 403);

        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        abort_if($service->clinic_id != session('active_clinic_id'), 403);

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
        abort_if($service->clinic_id != session('active_clinic_id'), 403);

        $service->update(['is_active' => ! $service->is_active]);

        $status = $service->is_active ? 'activado' : 'desactivado';

        return redirect()->route('services.index')
            ->with('success', "Servicio {$status}.");
    }
}
