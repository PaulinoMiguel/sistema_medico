<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->json('specialty_data')->nullable()->after('sexual_function');
            $table->text('neurological_exam')->nullable()->after('rectal_exam');
        });

        // Expand the type enum to include pediatrics/neurology types
        DB::statement("ALTER TABLE consultations MODIFY COLUMN type ENUM('initial','follow_up','pre_operative','post_operative','emergency','urodynamic','procedure','well_child','vaccination') DEFAULT 'initial'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE consultations MODIFY COLUMN type ENUM('initial','follow_up','pre_operative','post_operative','emergency','urodynamic','procedure') DEFAULT 'initial'");

        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['specialty_data', 'neurological_exam']);
        });
    }
};
