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
        Schema::create('rpd_schedule_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rpd_program_id')
                ->constrained('rpd_programs')
                ->cascadeOnDelete();

            $table->foreignId('rpd_curriculum_item_id')
                ->nullable()
                ->constrained('rpd_curriculum_items')
                ->nullOnDelete();

            $table->unsignedSmallInteger('week_number');

            $table->unsignedSmallInteger('theory_hours')->default(0);
            $table->unsignedSmallInteger('practice_hours')->default(0);

            $table->boolean('is_final_work')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpd_schedule_items');
    }
};
