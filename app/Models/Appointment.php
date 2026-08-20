<?php

namespace App\Models;

use App\Models\Scopes\MedicalRecordScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    /**
     * Tipos de turno admitidos, con su etiqueta.
     *
     * Fuente unica: la validacion, los formularios, el calendario y el
     * dashboard leen de aqui. Antes la lista estaba repetida en ocho
     * sitios y agregar un tipo obligaba a tocarlos todos.
     *
     * Debe coincidir con el enum de la columna appointments.type.
     */
    public const TYPES = [
        'first_visit' => 'Primera vez',
        'follow_up' => 'Control',
        'result' => 'Resultado',
        'flowmetry' => 'Flujometría',
        'pre_operative' => 'Pre-quirúrgico',
        'post_operative' => 'Post-quirúrgico',
        'urodynamic_study' => 'Urodinamia',
        'procedure' => 'Procedimiento',
        'surgical' => 'Cirugía',
        'emergency' => 'Urgencia',
    ];

    /** Etiquetas cortas para el calendario, donde el espacio es minimo. */
    public const SHORT_TYPES = [
        'first_visit' => '1ra vez',
        'follow_up' => 'Control',
        'result' => 'Resultado',
        'flowmetry' => 'Flujom.',
        'pre_operative' => 'Pre-Qx',
        'post_operative' => 'Post-Qx',
        'urodynamic_study' => 'Urodin.',
        'procedure' => 'Proced.',
        'surgical' => 'Cirugia',
        'emergency' => 'Urgencia',
    ];

    protected $guarded = ['id'];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new MedicalRecordScope());
    }

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'is_paid' => 'boolean',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function consultation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed', 'in_waiting_room']);
    }
}
