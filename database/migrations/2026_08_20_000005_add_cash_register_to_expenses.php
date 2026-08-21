<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ata un gasto a la caja de la que salio el dinero: el "gasto menor".
 *
 * Un gasto sin cash_register_id es un gasto normal de los que ya existian.
 * Con caja, ademas descuenta del efectivo esperado al cerrar, porque ese
 * dinero ya no esta en la gaveta.
 *
 * receipt_path guarda la foto del comprobante, opcional.
 *
 * Aditiva y reversible: los gastos existentes quedan sin caja, que es lo
 * que eran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('cash_register_id')->nullable()->after('clinic_id')
                ->constrained('cash_registers')->nullOnDelete();
            $table->string('receipt_path')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_id');
            $table->dropColumn('receipt_path');
        });
    }
};
