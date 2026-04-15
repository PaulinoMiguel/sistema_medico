<?php

namespace App\Models;

use App\Models\Scopes\MedicalRecordScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

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

    public function pediatricMeasurements(): HasMany
    {
        return $this->hasMany(PediatricMeasurement::class)->orderBy('measured_at');
    }

    /** True si el paciente tiene <37 semanas de EG al nacer. */
    public function isPreterm(): bool
    {
        return $this->gestational_age_weeks !== null && $this->gestational_age_weeks < 37;
    }

    public function ageInMonthsAt(\DateTimeInterface $date): float
    {
        return round($this->date_of_birth->floatDiffInMonths($date), 2);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function primaryDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_doctor_id');
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

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new MedicalRecordScope(
            useClinicPivot: true,
            doctorColumn: 'primary_doctor_id',
        ));

        static::creating(function (Patient $patient) {
            if (empty($patient->medical_record_number)) {
                $patient->medical_record_number = 'MRN-' . date('Ymd') . '-' . str_pad(
                    (string) (static::withoutGlobalScopes()->count() + 1), 4, '0', STR_PAD_LEFT
                );
            }
        });
    }
}
