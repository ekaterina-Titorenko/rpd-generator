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
        Schema::table('rpd_resources', function (Blueprint $table) {
            $table->string('source_type')->default('book')->after('type');
            $table->json('metadata')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('rpd_resources', function (Blueprint $table) {
            $table->dropColumn([
                'source_type',
                'metadata',
            ]);
        });
    }
};
