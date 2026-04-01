<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'prescription_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class)->orderBy('sort_order');
    }

    protected static function booted(): void
    {
        static::creating(function (Prescription $prescription) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = 'RX-' . date('Ymd') . '-' . str_pad(
                    (string) (static::count() + 1), 4, '0', STR_PAD_LEFT
                );
            }
        });
    }
}
