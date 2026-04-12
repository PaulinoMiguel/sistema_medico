<?php

namespace App\Models;

use App\Models\Scopes\MedicalRecordScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Doctor sees only own services. Secretary sees services from doctors
        // who work in any of her assigned clinics.
        static::addGlobalScope(new MedicalRecordScope(
            secretaryViaDoctorClinic: true,
        ));
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
