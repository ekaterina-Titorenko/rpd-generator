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
        Schema::table('rpd_schedule_items', function (Blueprint $table) {
            if (! Schema::hasColumn('rpd_schedule_items', 'content')) {
                $table->text('content')->nullable()->after('week_number');
            }

            if (! Schema::hasColumn('rpd_schedule_items', 'rpd_curriculum_item_id')) {
                $table->foreignId('rpd_curriculum_item_id')
                    ->nullable()
                    ->after('rpd_program_id')
                    ->constrained('rpd_curriculum_items')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('rpd_schedule_items', 'week_number')) {
                $table->unsignedSmallInteger('week_number')->default(1);
            }
        });
    }

    public function down(): void
    {
        Schema::table('rpd_schedule_items', function (Blueprint $table) {
            if (Schema::hasColumn('rpd_schedule_items', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
};
