<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
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

        // Cajas contadas que esperan que el doctor reciba el dinero. No
        // impiden abrir la del dia siguiente: si el doctor no vino, el
        // consultorio no puede quedarse parado.
        $pendingRegisters = CashRegister::where('clinic_id', $clinicId)
            ->where('status', 'pending_approval')
            ->with(['openedBy', 'closedBy'])
            ->latest('closed_at')
            ->get();

        $clinicDoctors = $this->clinicDoctors($clinicId);

        return view('cash-registers.index', compact('registers', 'openRegister', 'pendingRegisters', 'clinicDoctors'));
    }

    /** Doctores que atienden en la clinica: son quienes pueden recibir la caja. */
    private function clinicDoctors(int $clinicId)
    {
        return User::role(['doctor_admin', 'doctor_associate'])
            ->whereIn('status', ['active', 'passive'])
            ->whereHas('clinics', fn ($q) => $q->where('clinics.id', $clinicId))
            ->orderBy('name')
            ->get();
    }

    /**
     * Quien puede abrir la caja lo decide el permiso cash-register.open, que
     * ya filtra la ruta. Antes habia ademas un bloqueo por rol que impedia
     * abrirla a los doctores aunque tuvieran el permiso: el dia que la
     * secretaria falta, el consultorio se quedaba sin caja.
     */
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

        $cashRegister->load(['openedBy', 'closedBy', 'approvedBy', 'payments.patient', 'payments.service',
            'payments.receivedBy', 'payments.doctor', 'pettyExpenses.registeredBy']);

        $clinicDoctors = $this->clinicDoctors($cashRegister->clinic_id);

        return view('cash-registers.show', compact('cashRegister', 'clinicDoctors'));
    }

    public function close(Request $request, CashRegister $cashRegister)
    {
        // Cerrar lo gobierna el permiso cash-register.close, no el rol: si la
        // doctora abrio la caja porque no habia secretaria, tiene que poder
        // cerrarla ella misma.
        abort_if($cashRegister->clinic_id != session('active_clinic_id'), 403);
        abort_if(! $cashRegister->isOpen(), 400);

        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        // Se cuadra contra el efectivo, no contra el total cobrado: las
        // transferencias se cobran pero no dejan dinero en la gaveta.
        $expectedAmount = $cashRegister->expected_cash;

        $user = $request->user();

        // Si la cierra el propio doctor es porque llevo la caja el mismo: no
        // hay a quien entregarle el dinero, asi que queda recibida en el acto.
        // Pedirle un PIN a quien acaba de contar seria un tramite vacio.
        $seAutoAprueba = $user->isDoctor();

        $cashRegister->update([
            'closing_amount' => $validated['closing_amount'],
            'expected_amount' => $expectedAmount,
            'closing_notes' => $validated['closing_notes'] ?? null,
            'closed_by' => $user->id,
            'closed_at' => now(),
            'status' => $seAutoAprueba ? 'closed' : 'pending_approval',
            'approved_by' => $seAutoAprueba ? $user->id : null,
            'approved_at' => $seAutoAprueba ? now() : null,
        ]);

        return redirect()->route('cash-registers.show', $cashRegister)
            ->with('success', $seAutoAprueba
                ? 'Caja cerrada y recibida.'
                : 'Caja cerrada. Queda pendiente de que el doctor reciba conforme.');
    }

    /**
     * "Recibido conforme": el doctor confirma que recibio el dinero.
     *
     * Dos caminos, como se acordo. Desde su propia sesion no hace falta PIN
     * porque ya esta autenticado como el. Desde la pantalla de la secretaria
     * si, y ahi se verifica contra el PIN del doctor elegido.
     */
    public function approve(Request $request, CashRegister $cashRegister)
    {
        abort_if($cashRegister->clinic_id != session('active_clinic_id'), 403);
        abort_if(! $cashRegister->isPendingApproval(), 400, 'Esta caja no esta pendiente de aprobacion.');

        $user = $request->user();

        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'pin' => 'nullable|string',
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $doctor = User::find($validated['doctor_id']);
        abort_unless($doctor && $doctor->isDoctor(), 403, 'Solo un doctor puede recibir la caja.');

        // El doctor tiene que atender en esta clinica.
        abort_unless(
            $doctor->clinics()->where('clinics.id', $cashRegister->clinic_id)->exists(),
            403,
            'Ese doctor no atiende en esta clinica.',
        );

        if ($user->id !== $doctor->id) {
            $this->verifyAuthorizationPin($doctor, $validated['pin'] ?? '');
        }

        $cashRegister->update([
            'status' => 'closed',
            'approved_by' => $doctor->id,
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        return redirect()->route('cash-registers.show', $cashRegister)
            ->with('success', 'Caja recibida conforme.');
    }

    /**
     * El doctor devuelve la caja para que se recuente. Vuelve a abierta y se
     * limpia el conteo anterior, que ya no vale.
     */
    public function reject(Request $request, CashRegister $cashRegister)
    {
        abort_if($cashRegister->clinic_id != session('active_clinic_id'), 403);
        abort_if(! $cashRegister->isPendingApproval(), 400);

        // Devolver la caja se hace desde la sesion del doctor. A diferencia de
        // aprobar, no se resuelve con un PIN en el mostrador: es una decision,
        // no un tramite.
        abort_unless($request->user()->isDoctor(), 403, 'Solo un doctor puede devolver la caja.');

        $validated = $request->validate([
            'approval_notes' => 'required|string|max:500',
        ], [
            'approval_notes.required' => 'Explica por que se devuelve la caja.',
        ]);

        $cashRegister->update([
            'status' => 'open',
            'closing_amount' => null,
            'expected_amount' => null,
            'closed_by' => null,
            'closed_at' => null,
            'approval_notes' => $validated['approval_notes'],
        ]);

        return redirect()->route('cash-registers.index')
            ->with('success', 'Caja devuelta para recuento.');
    }

    /**
     * Acta de entrega, imprimible cuantas veces haga falta.
     *
     * Solo de cajas ya recibidas: es el comprobante de una entrega que ocurrio,
     * no un borrador. Y como la caja aprobada ya no admite cambios, el acta
     * sale siempre identica.
     */
    public function acta(CashRegister $cashRegister)
    {
        abort_if($cashRegister->clinic_id != session('active_clinic_id'), 403);
        abort_unless($cashRegister->isApproved() && $cashRegister->approved_at, 404,
            'Esta caja todavia no ha sido recibida, asi que no tiene acta.');

        $cashRegister->load(['clinic', 'openedBy', 'closedBy', 'approvedBy',
            'payments.patient', 'pettyExpenses.registeredBy']);

        // Las firmas se resuelven a ruta de disco si existe, para que el
        // documento tambien sirva si algun dia se genera como PDF.
        $firma = function (?string $ruta) {
            if (! $ruta) {
                return null;
            }
            $local = public_path('storage/' . $ruta);

            return is_file($local) ? asset('storage/' . $ruta) : null;
        };

        return view('cash-registers.acta', compact('cashRegister', 'firma'));
    }

    /**
     * Un PIN de pocos digitos se adivina probando, asi que se limita el numero
     * de intentos por doctor y por equipo.
     */
    private function verifyAuthorizationPin(User $doctor, string $pin): void
    {
        $clave = 'pin-caja:' . $doctor->id . ':' . request()->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $faltan = RateLimiter::availableIn($clave);
            throw ValidationException::withMessages([
                'pin' => "Demasiados intentos fallidos. Espera {$faltan} segundos.",
            ]);
        }

        if (! $doctor->hasAuthorizationPin()) {
            throw ValidationException::withMessages([
                'pin' => 'Este doctor todavia no configuro su PIN de autorizacion. '
                    . 'Puede hacerlo en Perfil > Impresion y firma, o aprobar desde su propia sesion.',
            ]);
        }

        if (! Hash::check($pin, $doctor->authorization_pin)) {
            RateLimiter::hit($clave, 300);
            throw ValidationException::withMessages(['pin' => 'PIN incorrecto.']);
        }

        RateLimiter::clear($clave);
    }
}
