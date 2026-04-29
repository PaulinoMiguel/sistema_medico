<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE consultations MODIFY COLUMN type ENUM('initial','follow_up','pre_operative','post_operative','emergency','urodynamic','procedure','well_child','vaccination','flowmetry') DEFAULT 'initial'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE consultations MODIFY COLUMN type ENUM('initial','follow_up','pre_operative','post_operative','emergency','urodynamic','procedure','well_child','vaccination') DEFAULT 'initial'");
    }
};
