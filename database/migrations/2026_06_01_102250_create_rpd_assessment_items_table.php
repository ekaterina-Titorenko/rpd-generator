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
        Schema::create('rpd_assessment_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rpd_program_id')
                ->constrained('rpd_programs')
                ->cascadeOnDelete();

            $table->enum('type', [
                'control_question',
                'final_practical_work',
            ]);

            $table->text('text');

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpd_assessment_items');
    }
};
