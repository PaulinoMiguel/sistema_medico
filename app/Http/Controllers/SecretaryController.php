<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SecretaryController extends Controller
{
    public function index()
    {
        $secretaries = User::where('role', 'secretary')
            ->with('clinics')
            ->get();

        return view('secretaries.index', compact('secretaries'));
    }

    public function create()
    {
        $clinics = Clinic::where('is_active', true)->get();

        return view('secretaries.create', compact('clinics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:50',
            'clinic_ids' => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id',
        ]);

        $secretary = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'secretary',
            'is_active' => true,
        ]);

        // Assign to selected clinics
        foreach ($validated['clinic_ids'] as $index => $clinicId) {
            $secretary->clinics()->attach($clinicId, [
                'is_primary' => $index === 0, // First clinic is primary
            ]);
        }

        return redirect()->route('secretaries.index')
            ->with('success', 'Secretaria creada exitosamente.');
    }

    public function show(User $secretary)
    {
        abort_if($secretary->role !== 'secretary', 404);

        $secretary->load('clinics');

        return view('secretaries.show', compact('secretary'));
    }

    public function edit(User $secretary)
    {
        abort_if($secretary->role !== 'secretary', 404);

        $clinics = Clinic::where('is_active', true)->get();
        $assignedClinicIds = $secretary->clinics->pluck('id')->toArray();

        return view('secretaries.edit', compact('secretary', 'clinics', 'assignedClinicIds'));
    }

    public function update(Request $request, User $secretary)
    {
        abort_if($secretary->role !== 'secretary', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $secretary->id,
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:50',
            'clinic_ids' => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id',
        ]);

        $secretary->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $secretary->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync clinics
        $syncData = [];
        foreach ($validated['clinic_ids'] as $index => $clinicId) {
            $syncData[$clinicId] = ['is_primary' => $index === 0];
        }
        $secretary->clinics()->sync($syncData);

        return redirect()->route('secretaries.index')
            ->with('success', 'Secretaria actualizada exitosamente.');
    }

    public function destroy(User $secretary)
    {
        abort_if($secretary->role !== 'secretary', 404);

        $secretary->update(['is_active' => false]);
        $secretary->clinics()->detach();

        return redirect()->route('secretaries.index')
            ->with('success', 'Secretaria desactivada.');
    }

    public function toggle(User $secretary)
    {
        abort_if($secretary->role !== 'secretary', 404);

        $secretary->update(['is_active' => ! $secretary->is_active]);

        $status = $secretary->is_active ? 'activada' : 'desactivada';

        return redirect()->route('secretaries.index')
            ->with('success', "Secretaria {$status}.");
    }
}
