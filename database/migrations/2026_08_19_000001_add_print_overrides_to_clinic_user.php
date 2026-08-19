<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite que la cabecera impresa cambie de una clinica a otra.
 *
 * Solo se guardan las diferencias: lo comun (logo, nombre, exequatur,
 * especialidad, correo, telefonos) sigue viviendo en el perfil del doctor.
 * Hoy se usa unicamente la clave 'print_address' (la linea del consultorio);
 * es JSON para poder sumar logo o telefono propio mas adelante sin volver
 * a migrar un servidor en produccion.
 *
 * Aditiva y reversible: con la columna vacia todo imprime igual que antes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_user', function (Blueprint $table) {
            $table->json('print_overrides')->nullable()->after('is_primary');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_user', function (Blueprint $table) {
            $table->dropColumn('print_overrides');
        });
    }
};
