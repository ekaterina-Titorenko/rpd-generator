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
        Schema::table('rpd_authors', function (Blueprint $table) {
            if (! Schema::hasColumn('rpd_authors', 'full_name')) {
                $table->string('full_name')->nullable()->after('rpd_program_id');
            }

            if (! Schema::hasColumn('rpd_authors', 'position')) {
                $table->string('position')->nullable()->after('full_name');
            }

            if (! Schema::hasColumn('rpd_authors', 'organization')) {
                $table->string('organization')->nullable()->after('position');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rpd_authors', function (Blueprint $table) {
            if (Schema::hasColumn('rpd_authors', 'organization')) {
                $table->dropColumn('organization');
            }

            if (Schema::hasColumn('rpd_authors', 'position')) {
                $table->dropColumn('position');
            }

            if (Schema::hasColumn('rpd_authors', 'full_name')) {
                $table->dropColumn('full_name');
            }
        });
    }
};
