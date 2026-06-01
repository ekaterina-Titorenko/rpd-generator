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
        Schema::create('rpd_programs', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->enum('direction', [
                'technical',
                'science',
                'social_humanitarian',
            ]);

            $table->string('complexity_level')->default('базовый');

            $table->unsignedSmallInteger('year')->default(2026);
            $table->string('smko_code')->nullable();

            $table->unsignedSmallInteger('total_hours')->default(36);

            $table->string('study_period')->default('1 год');
            $table->string('students_age')->default('14–18 лет');

            $table->text('education_form')->nullable();
            $table->text('study_mode')->nullable();

            $table->text('students_category')->nullable();
            $table->text('preparation_requirements')->nullable();

            $table->longText('program_description')->nullable();
            $table->longText('legal_basis')->nullable();

            $table->longText('relevance')->nullable();
            $table->longText('goal')->nullable();

            $table->json('learning_tasks')->nullable();
            $table->json('development_tasks')->nullable();

            $table->json('planned_results')->nullable();

            $table->json('personal_competencies')->nullable();
            $table->json('metasubject_competencies')->nullable();
            $table->json('subject_competencies')->nullable();

            $table->longText('organizational_conditions')->nullable();
            $table->longText('methodical_conditions')->nullable();
            $table->longText('material_conditions')->nullable();
            $table->longText('staffing_conditions')->nullable();

            $table->longText('attestation_forms')->nullable();
            $table->longText('control_criteria')->nullable();

            $table->json('knowledge_criteria')->nullable();
            $table->json('competency_criteria')->nullable();

            $table->longText('oral_control_criteria')->nullable();
            $table->longText('practical_work_criteria')->nullable();

            $table->enum('status', [
                'draft',
                'ready',
                'generated',
            ])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpd_programs');
    }
};
