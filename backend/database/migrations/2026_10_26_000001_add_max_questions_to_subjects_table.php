<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Jumlah soal yang ditampilkan per sesi ujian (default 20).
            // Bank soal boleh lebih banyak; setiap siswa mendapat soal acak sebanyak max_questions.
            $table->integer('max_questions')->default(20)->after('duration_minutes')
                  ->comment('Maks soal ditampilkan per sesi. Bank soal boleh lebih banyak untuk rotasi acak.');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('max_questions');
        });
    }
};
