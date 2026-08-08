<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->dropForeign(['pengguna_id']);
            $table->dropColumn('pengguna_id');
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->dropForeign(['pengguna_id']);
            $table->dropColumn('pengguna_id');
        });

        Schema::table('form_pengembalian', function (Blueprint $table) {
            $table->dropForeign(['pengguna_id']);
            $table->dropColumn('pengguna_id');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn('assigned_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->foreignId('pengguna_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->foreignId('pengguna_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('form_pengembalian', function (Blueprint $table) {
            $table->foreignId('pengguna_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
