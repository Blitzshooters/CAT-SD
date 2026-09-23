<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('grade');
            $table->integer('score');
            $table->integer('correct_count')->default(0);
            $table->integer('wrong_count')->default(0);
            $table->integer('unanswered_count')->default(0);
            $table->integer('total_questions')->default(20);
            $table->integer('time_spent_seconds')->default(0);
            $table->json('user_answers')->nullable();
            $table->json('proctoring_summary')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('proctoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('exam_result_id')->nullable();
            $table->string('violation_type');
            $table->text('description');
            $table->integer('question_index')->nullable();
            $table->bigInteger('timestamp_ms');
            $table->timestamps();

            $table->index(['user_id', 'exam_result_id']);
        });

        Schema::create('proctoring_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('exam_result_id')->nullable();
            $table->string('file_path');
            $table->bigInteger('timestamp_ms');
            $table->timestamps();

            $table->index(['user_id', 'exam_result_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proctoring_snapshots');
        Schema::dropIfExists('proctoring_logs');
        Schema::dropIfExists('exam_results');
    }
};
