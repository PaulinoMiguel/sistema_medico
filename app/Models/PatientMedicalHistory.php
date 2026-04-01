<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalHistory extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'allergies' => 'json',
            'chronic_conditions' => 'json',
            'family_history' => 'json',
            'surgical_history' => 'json',
            'current_medications' => 'json',
            'habits' => 'json',
            'urological_history' => 'json',
            'obstetric_gynecological' => 'json',
            'immunizations' => 'json',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
