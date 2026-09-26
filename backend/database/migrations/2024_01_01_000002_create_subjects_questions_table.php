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
            $table->integer('max_violations')->default(3)->comment('Batas pelanggaran AI proctoring');
            $table->string('remedy_code')->default('REMEDI123')->comment('Kode untuk reset pelanggaran / remedi');
            $table->string('icon_name')->default('school');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['grade']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->text('question_image')->nullable()->comment('URL / path / base64 gambar soal');
            $table->json('options')->comment('Array of 4 answer choices');
            $table->json('option_images')->nullable()->comment('Array of 4 answer choice images');
            $table->tinyInteger('correct_answer_index')->comment('0-3');
            $table->text('explanation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('subjects');
    }
};
