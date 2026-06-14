<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Banco de medicamentos por doctor. Plantilla reutilizable que la
        // doctora elige al crear una receta (Medicamento, Dosis, Duración,
        // Vía, Observación) para no escribirlo cada vez.
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');                       // Medicamento
            $table->string('dosage')->nullable();         // Dosis
            $table->string('duration')->nullable();       // Duración
            $table->string('route')->default('oral');     // Vía
            $table->text('instructions')->nullable();     // Observación
            $table->timestamps();

            $table->index(['doctor_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
