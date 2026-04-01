<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('consultation_date');
            $table->enum('type', [
                'initial', 'follow_up', 'pre_operative', 'post_operative',
                'emergency', 'urodynamic', 'procedure',
            ])->default('initial');
            $table->enum('status', ['in_progress', 'completed', 'signed'])->default('in_progress');

            // SOAP - Subjective
            $table->text('chief_complaint')->nullable();
            $table->text('history_present_illness')->nullable();
            $table->json('urinary_symptoms')->nullable();
            $table->json('sexual_function')->nullable();
            $table->text('review_of_systems')->nullable();

            // SOAP - Objective
            $table->json('vital_signs')->nullable();
            $table->text('physical_exam')->nullable();
            $table->text('genitourinary_exam')->nullable();
            $table->text('rectal_exam')->nullable();
            $table->text('abdomen_exam')->nullable();

            // SOAP - Assessment
            $table->text('assessment')->nullable();
            $table->json('diagnoses')->nullable();

            // SOAP - Plan
            $table->text('treatment_plan')->nullable();
            $table->text('diagnostic_orders')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->integer('follow_up_days')->nullable();
            $table->text('surgical_recommendation')->nullable();
            $table->text('referrals')->nullable();

            // Notes
            $table->text('private_notes')->nullable();

            $table->dateTime('signed_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'consultation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
