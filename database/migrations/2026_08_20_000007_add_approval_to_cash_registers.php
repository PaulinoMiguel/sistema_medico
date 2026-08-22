<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aprobacion de la caja por parte del doctor: "recibido conforme".
 *
 * El estado intermedio pending_approval significa que el dinero ya se conto
 * y se cerro, pero todavia no se entrego. Al aprobar pasa a closed, que a
 * partir de ahora significa cerrada y recibida.
 *
 * Las cajas ya cerradas se quedan en closed: se cerraron antes de que
 * existiera la aprobacion y no tiene sentido reabrirlas para aprobarlas.
 *
 * Aditiva: el valor nuevo del enum se agrega al final y las columnas son
 * nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cash_registers MODIFY COLUMN status ENUM('open','pending_approval','closed') NOT NULL DEFAULT 'open'");

        Schema::table('cash_registers', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('closed_by')
                ->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable()->after('closed_at');
            $table->text('approval_notes')->nullable()->after('closing_notes');
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'approval_notes']);
        });

        // Las que quedaron pendientes pasan a cerradas antes de quitar el
        // valor del enum, o el ALTER fallaria.
        DB::table('cash_registers')->where('status', 'pending_approval')->update(['status' => 'closed']);

        DB::statement("ALTER TABLE cash_registers MODIFY COLUMN status ENUM('open','closed') NOT NULL DEFAULT 'open'");
    }
};
