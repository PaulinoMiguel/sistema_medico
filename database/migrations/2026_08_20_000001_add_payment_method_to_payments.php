<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forma de pago del cobro: efectivo o transferencia.
 *
 * Distinto de 'channel', que dice QUIEN cobro (caja o el doctor directo).
 * Esto dice COMO se pago, y es lo que permite cuadrar la gaveta: una
 * transferencia no deja efectivo en caja.
 *
 * Los cobros existentes quedan como 'cash', que es lo correcto: hasta
 * ahora todo lo registrado fue en efectivo.
 *
 * Aditiva y reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('cash')->after('channel');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
