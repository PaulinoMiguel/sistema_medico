<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $search = $request->get('search');

        $prescriptions = Prescription::where('clinic_id', $clinicId)
            ->with(['patient', 'doctor', 'items'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('patient', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('prescription_date')
            ->paginate(20)
            ->withQueryString();

        return view('prescriptions.index', compact('prescriptions', 'search'));
    }

    public function create(Request $request)
    {
        $clinicId = session('active_clinic_id');
        $patients = Patient::whereHas('clinics', fn ($q) => $q->where('clinics.id', $clinicId))
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();

        $selectedPatientId = $request->get('patient_id');
        $consultationId = $request->get('consultation_id');

        return view('prescriptions.create', compact('patients', 'selectedPatientId', 'consultationId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'diagnosis' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medication_name' => 'required|string|max:255',
            'items.*.dosage' => 'required|string|max:255',
            'items.*.frequency' => 'required|string|max:255',
            'items.*.duration' => 'nullable|string|max:255',
            'items.*.route' => 'required|string|max:50',
            'items.*.instructions' => 'nullable|string',
            'items.*.quantity' => 'nullable|integer|min:1',
        ]);

        $prescription = Prescription::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $request->user()->id,
            'clinic_id' => session('active_clinic_id'),
            'consultation_id' => $validated['consultation_id'] ?? null,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => $validated['diagnosis'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $index => $item) {
            $prescription->items()->create([
                ...$item,
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Receta creada exitosamente.');
    }

    public function show(Prescription $prescription)
    {
        $prescription->load(['patient', 'doctor', 'clinic', 'consultation', 'items']);

        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        if ($prescription->status === 'cancelled') {
            return redirect()->route('prescriptions.show', $prescription)
                ->with('error', 'Esta receta fue cancelada y no puede editarse.');
        }

        $prescription->load(['patient', 'items']);
        $clinicId = session('active_clinic_id');
        $patients = Patient::whereHas('clinics', fn ($q) => $q->where('clinics.id', $clinicId))
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();

        return view('prescriptions.edit', compact('prescription', 'patients'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        if ($prescription->status === 'cancelled') {
            return redirect()->back()->with('error', 'Receta cancelada, no se puede modificar.');
        }

        $validated = $request->validate([
            'diagnosis' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,expired,cancelled',
            'items' => 'required|array|min:1',
            'items.*.medication_name' => 'required|string|max:255',
            'items.*.dosage' => 'required|string|max:255',
            'items.*.frequency' => 'required|string|max:255',
            'items.*.duration' => 'nullable|string|max:255',
            'items.*.route' => 'required|string|max:50',
            'items.*.instructions' => 'nullable|string',
            'items.*.quantity' => 'nullable|integer|min:1',
        ]);

        $prescription->update([
            'diagnosis' => $validated['diagnosis'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'] ?? $prescription->status,
        ]);

        // Replace all items
        $prescription->items()->delete();
        foreach ($validated['items'] as $index => $item) {
            $prescription->items()->create([
                ...$item,
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Receta actualizada.');
    }

    public function pdf(Prescription $prescription)
    {
        $prescription->load(['patient', 'doctor', 'clinic', 'items']);

        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription'));
        $pdf->setPaper('letter');

        return $pdf->stream("receta-{$prescription->prescription_number}.pdf");
    }

    public function createFromConsultation(Consultation $consultation)
    {
        return redirect()->route('prescriptions.create', [
            'patient_id' => $consultation->patient_id,
            'consultation_id' => $consultation->id,
        ]);
    }
}
