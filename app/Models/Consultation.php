<?php

namespace App\Models;

use App\Models\Scopes\MedicalRecordScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new MedicalRecordScope());
    }

    protected function casts(): array
    {
        return [
            'consultation_date' => 'datetime',
            'signed_at' => 'datetime',
            'vital_signs' => 'json',
            'urinary_symptoms' => 'json',
            'sexual_function' => 'json',
            'diagnoses' => 'json',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    public function canEdit(): bool
    {
        return $this->status !== 'signed';
    }
}
