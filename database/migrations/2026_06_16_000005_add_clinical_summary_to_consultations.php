<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "Resumen clínico" (referencia/interconsulta) de la consulta inicial.
        // JSON con: insurer_id, insurer_name, summary, diagnosis, diagnosis_type,
        // studies_done, previous_treatments y procedures (snapshot de los códigos
        // elegidos: id/code/description) para que el documento no cambie si luego
        // editan el catálogo.
        Schema::table('consultations', function (Blueprint $table) {
            $table->json('clinical_summary')->nullable()->after('referrals');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn('clinical_summary');
        });
    }
};
