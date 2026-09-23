<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->tinyInteger('grade')->comment('Kelas 1-6');
            $table->integer('duration_minutes')->default(45);
            $table->timestamps();

            $table->index(['grade']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->json('options')->comment('Array of 4 answer choices');
            $table->tinyInteger('correct_answer_index')->comment('0-3');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('subjects');
    }
};
