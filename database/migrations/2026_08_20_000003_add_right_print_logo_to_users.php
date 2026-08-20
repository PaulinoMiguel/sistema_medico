<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Segundo logo para la cabecera impresa, alineado a la derecha.
 *
 * El de la izquierda (print_logo_path) es el del doctor; este suele ser el
 * del hospital o centro donde atiende. Se guarda junto al doctor y no en la
 * clinica porque es el doctor quien lo administra desde su perfil de
 * impresion, igual que el otro.
 *
 * Aditiva y reversible: con la columna vacia la cabecera imprime igual que
 * hoy, con la celda derecha en blanco.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('print_logo_right_path')->nullable()->after('print_logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('print_logo_right_path');
        });
    }
};
