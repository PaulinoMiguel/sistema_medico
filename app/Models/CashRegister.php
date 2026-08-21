<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'closing_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getTotalCollectedAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /** Cobrado en efectivo: lo unico que deberia estar fisicamente en la gaveta. */
    public function getTotalCashAttribute(): float
    {
        return (float) $this->payments()->where('payment_method', 'cash')->sum('amount');
    }

    public function getTotalTransferAttribute(): float
    {
        return (float) $this->payments()->where('payment_method', 'transfer')->sum('amount');
    }

    /** Gastos menores pagados con dinero de esta caja. */
    public function pettyExpenses(): HasMany
    {
        return $this->hasMany(Expense::class)->orderBy('id');
    }

    public function getTotalPettyExpensesAttribute(): float
    {
        return (float) $this->pettyExpenses()->sum('amount');
    }

    /**
     * Lo que debe haber en la gaveta al cerrar: apertura, mas el efectivo
     * cobrado, menos lo que se saco para gastos menores.
     *
     * Las transferencias se cobran pero no pasan por la caja, asi que
     * incluirlas produciria un faltante falso en cada cierre. Los gastos
     * menores son el caso inverso: ese dinero ya salio de la gaveta.
     */
    public function getExpectedCashAttribute(): float
    {
        return (float) $this->opening_amount + $this->total_cash - $this->total_petty_expenses;
    }

    /**
     * Efectivo que hay ahora mismo en la gaveta. Es el tope de un gasto
     * menor: no se puede sacar mas de lo que hay.
     */
    public function getAvailableCashAttribute(): float
    {
        return $this->expected_cash;
    }
}
