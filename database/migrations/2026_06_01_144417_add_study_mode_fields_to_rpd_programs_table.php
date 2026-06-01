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
            $table->string('lessons_per_week')->default('1-2 раза')->after('education_format');
            $table->unsignedSmallInteger('academic_hours_per_lesson')->default(2)->after('lessons_per_week');
            $table->unsignedSmallInteger('academic_hour_minutes')->default(45)->after('academic_hours_per_lesson');
        });
    }

    public function down(): void
    {
        Schema::table('rpd_programs', function (Blueprint $table) {
            $table->dropColumn([
                'lessons_per_week',
                'academic_hours_per_lesson',
                'academic_hour_minutes',
            ]);
        });
    }
};
