<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El modelo original (cada aseguradora con su lista de códigos) era
     * incorrecto. El catálogo real es: procedimiento × aseguradora -> código,
     * simón, monto. Se reemplaza por procedures + procedure_insurer.
     */
    public function up(): void
    {
        Schema::dropIfExists('insurer_procedures');
    }

    public function down(): void
    {
        Schema::create('insurer_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurer_id')->constrained('insurers')->cascadeOnDelete();
            $table->string('code');
            $table->string('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
