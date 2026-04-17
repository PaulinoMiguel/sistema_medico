<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPassive(): bool
    {
        return $this->status === 'passive';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Activo',
            'passive' => 'Pasivo',
            'inactive' => 'Inactivo',
            default => 'Desconocido',
        };
    }

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'clinic_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'doctor_patient', 'doctor_id', 'patient_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Wrappers para mantener compatibilidad con el codigo existente.
     * La fuente de verdad ahora son los roles de spatie.
     */
    public function isDoctor(): bool
    {
        return $this->hasAnyRole(['doctor_admin', 'doctor_associate']);
    }

    public function isSecretary(): bool
    {
        return $this->hasAnyRole(['secretary_full', 'secretary_limited']);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return null;
    }

    /**
     * Etiqueta legible del rol principal del usuario, en espanol.
     * Reemplaza el uso directo del enum `role` que ya no existe.
     */
    public function getRoleLabelAttribute(): string
    {
        $map = [
            'doctor_admin' => 'Doctor administrador',
            'doctor_associate' => 'Doctor asociado',
            'secretary_full' => 'Secretaria',
            'secretary_limited' => 'Secretaria',
            'nurse' => 'Enfermera',
        ];

        $primary = $this->roles->first();

        if (!$primary) {
            return 'Sin rol';
        }

        return $map[$primary->name] ?? $primary->name;
    }
}
