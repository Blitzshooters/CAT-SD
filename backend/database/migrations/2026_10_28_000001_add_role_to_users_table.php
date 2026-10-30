<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('student')->after('is_admin')->comment('admin|teacher|student');
            });

            // Sinkronisasi data user lama
            DB::table('users')->where('is_admin', true)->where('username', 'admin')->update(['role' => 'admin']);
            DB::table('users')->where('is_admin', true)->where('username', '!=', 'admin')->update(['role' => 'teacher']);
            DB::table('users')->where('is_admin', false)->update(['role' => 'student']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
