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
            $table->longText('review_comment')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('rpd_programs', function (Blueprint $table) {
            $table->dropColumn('review_comment');
        });
    }
};
