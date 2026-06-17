<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enlaza al paciente con una aseguradora del catálogo. Se conserva
        // insurance_provider (texto) como denormalización para mostrar y para
        // los pacientes viejos sin enlace. nullOnDelete: borrar una aseguradora
        // no rompe la ficha del paciente.
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('insurer_id')->nullable()->after('insurance_provider')
                ->constrained('insurers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('insurer_id');
        });
    }
};
