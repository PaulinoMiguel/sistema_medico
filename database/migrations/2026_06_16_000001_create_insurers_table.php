<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aseguradoras (ARS). Catálogo UNIVERSAL: una sola lista para todo el
        // sistema, mantenida por la secretaria. Cada aseguradora tiene su propio
        // catálogo de códigos (ver insurer_procedures).
        Schema::create('insurers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurers');
    }
};
