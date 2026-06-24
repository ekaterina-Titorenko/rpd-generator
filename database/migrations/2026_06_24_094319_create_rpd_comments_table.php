<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rpd_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpd_program_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('type')->default('comment');
            $table->text('message');
            $table->timestamps();

            $table->index(['rpd_program_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpd_comments');
    }
};