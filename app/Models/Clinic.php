<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'settings' => 'json',
            'expense_split_config' => 'json',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Doctores activos asociados a la clinica, ordenados por id.
     */
    public function doctors()
    {
        return $this->users()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['doctor_admin', 'doctor_associate']))
            ->where('users.status', 'active')
            ->orderBy('users.id');
    }

    /**
     * Retorna el porcentaje (0..1) que le corresponde al doctor del pool compartido
     * segun el metodo de split configurado en la clinica.
     *
     * - equal: reparte entre todos los doctores activos por igual.
     * - percentage: usa expense_split_config = { doctor_id: porcentaje_0_100, ... }.
     * - by_income: placeholder por ahora; cae a equal hasta que se implemente.
     */
    public function splitPercentageFor(User $doctor): float
    {
        $doctorIds = $this->doctors()->pluck('users.id')->all();

        if (! in_array($doctor->id, $doctorIds, true) || empty($doctorIds)) {
            return 0.0;
        }

        return match ($this->expense_split_method) {
            'percentage' => (float) (($this->expense_split_config[$doctor->id] ?? 0) / 100),
            default => 1.0 / count($doctorIds),
        };
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'clinic_patient')
            ->withPivot('clinic_record_number', 'is_active')
            ->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
