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
            $table->string('education_format')->default('mixed')->after('students_age');
        });
    }

    public function down(): void
    {
        Schema::table('rpd_programs', function (Blueprint $table) {
            $table->dropColumn('education_format');
        });
    }
};
