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
        Schema::table('rpd_content_sections', function (Blueprint $table) {
            $table->foreignId('rpd_curriculum_item_id')
                ->nullable()
                ->after('rpd_program_id')
                ->constrained('rpd_curriculum_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rpd_content_sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rpd_curriculum_item_id');
        });
    }
};
