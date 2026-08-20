<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega los tipos de turno "Resultado" y "Flujometria".
 *
 * Se anaden al final del enum para no alterar los valores existentes.
 * Aditiva: ningun turno ya guardado cambia.
 *
 * El down() solo puede revertirse si no quedan turnos usando los tipos
 * nuevos; por eso primero los reasigna a 'follow_up'.
 */
return new class extends Migration
{
    private const TIPOS_NUEVOS = "'first_visit','follow_up','pre_operative','post_operative','urodynamic_study','procedure','emergency','surgical','result','flowmetry'";

    private const TIPOS_ANTERIORES = "'first_visit','follow_up','pre_operative','post_operative','urodynamic_study','procedure','emergency','surgical'";

    public function up(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY COLUMN type ENUM(" . self::TIPOS_NUEVOS . ") NOT NULL DEFAULT 'first_visit'");
    }

    public function down(): void
    {
        DB::table('appointments')->whereIn('type', ['result', 'flowmetry'])->update(['type' => 'follow_up']);

        DB::statement("ALTER TABLE appointments MODIFY COLUMN type ENUM(" . self::TIPOS_ANTERIORES . ") NOT NULL DEFAULT 'first_visit'");
    }
};
