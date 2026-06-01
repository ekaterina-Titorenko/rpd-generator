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
            $table->string('type')->default('main_recommended')->change();
        });
    }

    public function down(): void
    {
        Schema::table('rpd_resources', function (Blueprint $table) {
            $table->enum('type', [
                'main',
                'additional',
                'internet',
            ])->default('main')->change();
        });
    }
};
