<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Códigos (procedimientos/estudios) por aseguradora. Cada aseguradora
        // maneja su propio catálogo de códigos CUPS con su descripción.
        Schema::create('insurer_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurer_id')->constrained('insurers')->cascadeOnDelete();
            $table->string('code');                 // Código CUPS
            $table->string('description');          // Procedimiento / estudio
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['insurer_id', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurer_procedures');
    }
};
