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
        Schema::table('users', function (Blueprint $table) {
            $table->string('print_logo_path')->nullable()->after('digital_signature_path');
            $table->string('print_address')->nullable()->after('print_logo_path');
            $table->string('print_website')->nullable()->after('print_address');
            $table->text('print_extra_header')->nullable()->after('print_website');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['print_logo_path', 'print_address', 'print_website', 'print_extra_header']);
        });
    }
};
