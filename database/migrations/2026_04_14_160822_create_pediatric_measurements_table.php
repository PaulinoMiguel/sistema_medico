<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pediatric_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();

            $table->date('measured_at');

            // Edad en meses decimales al momento de la medicion (cronologica).
            $table->decimal('age_months', 6, 2);
            // Si el paciente es prematuro y la edad corregida aplica (< 24 meses post-termino).
            $table->decimal('corrected_age_months', 6, 2)->nullable();

            $table->decimal('weight_kg', 6, 3)->nullable();
            $table->decimal('height_cm', 6, 2)->nullable();
            $table->decimal('head_circumference_cm', 5, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();

            // Z-scores calculados (cacheados para no recalcular al graficar).
            $table->decimal('weight_z', 5, 2)->nullable();
            $table->decimal('height_z', 5, 2)->nullable();
            $table->decimal('head_circumference_z', 5, 2)->nullable();
            $table->decimal('bmi_z', 5, 2)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pediatric_measurements');
    }
};
