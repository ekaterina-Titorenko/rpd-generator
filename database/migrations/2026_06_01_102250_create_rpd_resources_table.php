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
        Schema::create('rpd_resources', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rpd_program_id')
                ->constrained('rpd_programs')
                ->cascadeOnDelete();

            $table->enum('type', [
                'main_literature',
                'additional_literature',
                'internet_resource',
            ]);

            $table->text('title');
            $table->string('url')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpd_resources');
    }
};
