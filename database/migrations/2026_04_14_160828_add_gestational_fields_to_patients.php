<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Datos perinatales (solo relevantes para pacientes pediatricos).
            $table->unsignedTinyInteger('gestational_age_weeks')->nullable()->after('date_of_birth');
            $table->decimal('birth_weight_kg', 5, 3)->nullable()->after('gestational_age_weeks');
            $table->decimal('birth_length_cm', 5, 2)->nullable()->after('birth_weight_kg');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['gestational_age_weeks', 'birth_weight_kg', 'birth_length_cm']);
        });
    }
};
