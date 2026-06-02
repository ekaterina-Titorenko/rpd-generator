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
        if (Schema::hasTable('rpd_schedule_items')) {
            return;
        }

        Schema::create('rpd_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpd_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rpd_curriculum_item_id')->nullable()->constrained('rpd_curriculum_items')->nullOnDelete();
            $table->unsignedSmallInteger('week_number');
            $table->text('content')->nullable();
            $table->timestamps();

            $table->unique(['rpd_program_id', 'rpd_curriculum_item_id', 'week_number'], 'rpd_schedule_unique');
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
