<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La dosis deja de ser obligatoria: en la receta solo el medicamento lo es.
 *
 * Relaja una restriccion, asi que no puede fallar por datos existentes: las
 * filas que ya tienen dosis siguen igual. El down() vuelve a exigirla, y
 * antes rellena con cadena vacia las que hayan quedado nulas, porque si no
 * el ALTER fallaria.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->string('dosage')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('prescription_items')->whereNull('dosage')->update(['dosage' => '']);

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->string('dosage')->nullable(false)->change();
        });
    }
};
