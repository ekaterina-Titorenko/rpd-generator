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
        Schema::table('rpd_curriculum_items', function (Blueprint $table) {
            $table->enum('type', [
                'section',
                'topic',
                'final_work',
            ])->default('topic')->after('rpd_program_id');

            $table->foreignId('parent_id')
                ->nullable()
                ->after('type')
                ->constrained('rpd_curriculum_items')
                ->nullOnDelete();

            $table->foreignId('control_form_id')
                ->nullable()
                ->after('control_form')
                ->constrained('rpd_control_forms')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rpd_curriculum_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('control_form_id');
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('type');
        });
    }
};
