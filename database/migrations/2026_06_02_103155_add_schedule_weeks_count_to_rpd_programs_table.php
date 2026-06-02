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
        Schema::table('rpd_programs', function (Blueprint $table) {
            $table->unsignedSmallInteger('schedule_weeks_count')
                ->nullable()
                ->after('academic_hour_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('rpd_programs', function (Blueprint $table) {
            $table->dropColumn('schedule_weeks_count');
        });
    }
};
