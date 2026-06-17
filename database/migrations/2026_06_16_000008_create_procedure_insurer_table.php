<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cruce procedimiento × aseguradora: el código, "simón" y monto que
        // cada ARS asigna a cada procedimiento.
        Schema::create('procedure_insurer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnDelete();
            $table->foreignId('insurer_id')->constrained('insurers')->cascadeOnDelete();
            $table->string('code')->nullable();             // CODIGO
            $table->string('simon')->nullable();            // SIMON (libre)
            $table->decimal('monto', 12, 2)->nullable();    // MONTO (referencia interna)
            $table->timestamps();

            $table->unique(['procedure_id', 'insurer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_insurer');
    }
};
