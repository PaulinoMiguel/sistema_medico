<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First drop the FK and column for clinic_id, then add doctor_id.
        // The previous index ['clinic_id', 'is_active'] gets dropped with the column.
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropIndex(['clinic_id', 'is_active']);
            $table->dropColumn('clinic_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('doctor_id')
                ->after('id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->index(['doctor_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropIndex(['doctor_id', 'is_active']);
            $table->dropColumn('doctor_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('clinic_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['clinic_id', 'is_active']);
        });
    }
};
