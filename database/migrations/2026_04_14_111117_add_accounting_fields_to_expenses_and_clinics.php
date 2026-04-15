<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('owner_doctor_id')
                ->nullable()
                ->after('registered_by')
                ->constrained('users')
                ->nullOnDelete();
            $table->index(['clinic_id', 'owner_doctor_id', 'expense_date'], 'expenses_clinic_owner_date_idx');
        });

        Schema::table('clinics', function (Blueprint $table) {
            $table->enum('expense_split_method', ['equal', 'percentage', 'by_income'])
                ->default('equal')
                ->after('settings');
            $table->json('expense_split_config')->nullable()->after('expense_split_method');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_clinic_owner_date_idx');
            $table->dropConstrainedForeignId('owner_doctor_id');
        });

        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['expense_split_method', 'expense_split_config']);
        });
    }
};
