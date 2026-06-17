<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Insurer extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function procedures(): BelongsToMany
    {
        return $this->belongsToMany(Procedure::class, 'procedure_insurer')
            ->withPivot('code', 'simon', 'monto')
            ->withTimestamps();
    }
}
