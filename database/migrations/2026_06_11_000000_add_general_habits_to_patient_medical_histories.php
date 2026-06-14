<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_medical_histories', function (Blueprint $table) {
            $table->json('general_habits')->nullable()->after('habits');
        });
    }

    public function down(): void
    {
        Schema::table('patient_medical_histories', function (Blueprint $table) {
            $table->dropColumn('general_habits');
        });
    }
};
