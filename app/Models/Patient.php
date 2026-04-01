<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'clinic_patient')
            ->withPivot('clinic_record_number', 'is_active')
            ->withTimestamps();
    }

    public function medicalHistory(): HasOne
    {
        return $this->hasOne(PatientMedicalHistory::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function getFullNameAttribute(): string
    {
        $name = "{$this->first_name} {$this->last_name}";
        if ($this->second_last_name) {
            $name .= " {$this->second_last_name}";
        }
        return $name;
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            if (empty($patient->medical_record_number)) {
                $patient->medical_record_number = 'MRN-' . date('Ymd') . '-' . str_pad(
                    (string) (static::count() + 1), 4, '0', STR_PAD_LEFT
                );
            }
        });
    }
}
