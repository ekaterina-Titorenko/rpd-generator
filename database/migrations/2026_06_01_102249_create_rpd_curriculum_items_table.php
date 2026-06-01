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
        Schema::create('rpd_curriculum_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rpd_program_id')
                ->constrained('rpd_programs')
                ->cascadeOnDelete();

            $table->string('number')->nullable();
            $table->string('title');

            $table->unsignedSmallInteger('total_hours')->default(0);
            $table->unsignedSmallInteger('theory_hours')->default(0);
            $table->unsignedSmallInteger('practice_hours')->default(0);

            $table->string('control_form')->nullable();

            $table->boolean('is_final_work')->default(false);

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpd_curriculum_items');
    }
};
