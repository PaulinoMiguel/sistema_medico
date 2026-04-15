<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->default('MediApp');
            $table->string('brand_tagline')->default('Sistema Medico');
            $table->string('primary_color', 7)->default('#2563eb');
            $table->string('logo_path')->nullable();
            $table->json('modules')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_settings');
    }
};
