<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function index()
    {
        $clinicId = session('active_clinic_id');

        $registers = CashRegister::where('clinic_id', $clinicId)
            ->with(['openedBy', 'closedBy'])
            ->latest('opened_at')
            ->paginate(15);

        $openRegister = CashRegister::where('clinic_id', $clinicId)
            ->where('status', 'open')
            ->first();

        return view('cash-registers.index', compact('registers', 'openRegister'));
    }

    public function open(Request $request)
    {
        $clinicId = session('active_clinic_id');

        // Check if there's already an open register
        $existing = CashRegister::where('clinic_id', $clinicId)
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            return redirect()->route('cash-registers.index')
                ->withErrors(['Ya hay una caja abierta. Ciérrala antes de abrir otra.']);
        }

        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        CashRegister::create([
            'clinic_id' => $clinicId,
            'opened_by' => auth()->id(),
            'opening_amount' => $validated['opening_amount'],
            'opened_at' => now(),
            'status' => 'open',
        ]);

        return redirect()->route('cash-registers.index')
            ->with('success', 'Caja abierta exitosamente.');
    }

    public function show(CashRegister $cashRegister)
    {
        abort_if($cashRegister->clinic_id != session('active_clinic_id'), 403);

        $cashRegister->load(['openedBy', 'closedBy', 'payments.patient', 'payments.service']);

        return view('cash-registers.show', compact('cashRegister'));
    }

    public function close(Request $request, CashRegister $cashRegister)
    {
        abort_if($cashRegister->clinic_id != session('active_clinic_id'), 403);
        abort_if(! $cashRegister->isOpen(), 400);

        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        $totalCollected = $cashRegister->total_collected;
        $expectedAmount = $cashRegister->opening_amount + $totalCollected;

        $cashRegister->update([
            'closing_amount' => $validated['closing_amount'],
            'expected_amount' => $expectedAmount,
            'closing_notes' => $validated['closing_notes'],
            'closed_by' => auth()->id(),
            'closed_at' => now(),
            'status' => 'closed',
        ]);

        return redirect()->route('cash-registers.show', $cashRegister)
            ->with('success', 'Caja cerrada exitosamente.');
    }
}
