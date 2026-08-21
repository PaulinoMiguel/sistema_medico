<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            ->with(['openedBy', 'payments.patient', 'payments.receivedBy', 'payments.doctor',
                'pettyExpenses.registeredBy'])
            ->first();

        return view('cash-registers.index', compact('registers', 'openRegister'));
    }

    public function open(Request $request)
    {
        abort_if($request->user()->isDoctor(), 403, 'Solo el personal de caja puede abrir la caja.');

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

    /**
     * Gasto menor: dinero que sale de la gaveta de la caja abierta.
     *
     * Se guarda como un gasto normal atado a la caja, asi entra en los
     * resumenes financieros y en el reparto entre doctores sin tratarlo
     * como un caso aparte. La categoria se resuelve sola para que la
     * secretaria no necesite acceso al catalogo de categorias.
     */
    public function storePettyExpense(Request $request)
    {
        $clinicId = session('active_clinic_id');

        $caja = CashRegister::where('clinic_id', $clinicId)->where('status', 'open')->first();

        if (! $caja) {
            return redirect()->route('cash-registers.index')
                ->withErrors(['No hay una caja abierta. Abre la caja antes de registrar un gasto.']);
        }

        $validated = $request->validate([
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'receipt' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'concept.required' => 'Escribe en que se gasto el dinero.',
            'amount.required' => 'Indica el monto del gasto.',
            'amount.min' => 'El monto debe ser mayor que cero.',
        ]);

        // El tope es el efectivo que hay en la gaveta: no se puede sacar
        // mas de lo que hay. Se recalcula aqui y no en el formulario porque
        // entre que se abrio la pantalla y se envio pudo cambiar.
        $disponible = $caja->available_cash;

        if ($validated['amount'] > $disponible) {
            throw ValidationException::withMessages([
                'amount' => 'Solo hay $' . number_format($disponible, 2) . ' en efectivo en la caja.',
            ]);
        }

        $categoria = ExpenseCategory::firstOrCreate(
            ['clinic_id' => $clinicId, 'name' => 'Gasto menor'],
            ['is_active' => true],
        );

        Expense::create([
            'clinic_id' => $clinicId,
            'cash_register_id' => $caja->id,
            'expense_category_id' => $categoria->id,
            'registered_by' => $request->user()->id,
            // Sin dueno: es un gasto del consultorio, no personal de un doctor.
            'owner_doctor_id' => null,
            'expense_date' => now()->toDateString(),
            'concept' => $validated['concept'],
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
            'receipt_path' => $request->hasFile('receipt')
                ? $request->file('receipt')->store('receipts', 'public')
                : null,
        ]);

        return redirect()->route('cash-registers.index')
            ->with('success', 'Gasto menor registrado. Se descuenta del efectivo de la caja.');
    }

    public function show(CashRegister $cashRegister)
    {
        abort_if($cashRegister->clinic_id != session('active_clinic_id'), 403);

        $cashRegister->load(['openedBy', 'closedBy', 'payments.patient', 'payments.service',
            'payments.receivedBy', 'payments.doctor', 'pettyExpenses.registeredBy']);

        return view('cash-registers.show', compact('cashRegister'));
    }

    public function close(Request $request, CashRegister $cashRegister)
    {
        abort_if($request->user()->isDoctor(), 403, 'Solo el personal de caja puede cerrar la caja.');
        abort_if($cashRegister->clinic_id != session('active_clinic_id'), 403);
        abort_if(! $cashRegister->isOpen(), 400);

        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        // Se cuadra contra el efectivo, no contra el total cobrado: las
        // transferencias se cobran pero no dejan dinero en la gaveta.
        $expectedAmount = $cashRegister->expected_cash;

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
