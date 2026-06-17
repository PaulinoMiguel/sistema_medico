<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Procedure extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Aseguradoras con el código/simón/monto que cada una asigna a este
     * procedimiento (tabla cruce procedure_insurer).
     */
    public function insurers(): BelongsToMany
    {
        return $this->belongsToMany(Insurer::class, 'procedure_insurer')
            ->withPivot('code', 'simon', 'monto')
            ->withTimestamps();
    }
}
