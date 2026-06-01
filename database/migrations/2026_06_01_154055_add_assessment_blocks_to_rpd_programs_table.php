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
            $table->longText('control_survey_materials')->nullable()->after('attestation_forms');
            $table->longText('final_practical_work_materials')->nullable()->after('control_survey_materials');
            $table->longText('project_topics')->nullable()->after('final_practical_work_materials');
        });
    }

    public function down(): void
    {
        Schema::table('rpd_programs', function (Blueprint $table) {
            $table->dropColumn([
                'control_survey_materials',
                'final_practical_work_materials',
                'project_topics',
            ]);
        });
    }
};
