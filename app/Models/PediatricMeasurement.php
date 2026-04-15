<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PediatricMeasurement extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'measured_at' => 'date',
            'age_months' => 'decimal:2',
            'corrected_age_months' => 'decimal:2',
            'weight_kg' => 'decimal:3',
            'height_cm' => 'decimal:2',
            'head_circumference_cm' => 'decimal:2',
            'bmi' => 'decimal:2',
            'weight_z' => 'decimal:2',
            'height_z' => 'decimal:2',
            'head_circumference_z' => 'decimal:2',
            'bmi_z' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
