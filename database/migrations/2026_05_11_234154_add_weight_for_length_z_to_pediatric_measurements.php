<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pediatric_measurements', function (Blueprint $table) {
            $table->decimal('weight_for_length_z', 5, 2)->nullable()->after('bmi_z');
        });
    }

    public function down(): void
    {
        Schema::table('pediatric_measurements', function (Blueprint $table) {
            $table->dropColumn('weight_for_length_z');
        });
    }
};
