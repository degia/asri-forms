<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nik', 50)->nullable()->index();
            $table->string('department')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('site')->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('status')->constrained('employees')->nullOnDelete();
        });

        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->foreignId('pengguna_employee_id')->nullable()->after('pengguna_id')->constrained('employees')->nullOnDelete();
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->foreignId('pengguna_employee_id')->nullable()->after('pengguna_id')->constrained('employees')->nullOnDelete();
        });

        Schema::table('form_pengembalian', function (Blueprint $table) {
            $table->foreignId('pengguna_employee_id')->nullable()->after('pengguna_id')->constrained('employees')->nullOnDelete();
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('assigned_employee_id')->nullable()->after('assigned_user_id')->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['assigned_employee_id']);
            $table->dropColumn('assigned_employee_id');
        });

        Schema::table('form_pengembalian', function (Blueprint $table) {
            $table->dropForeign(['pengguna_employee_id']);
            $table->dropColumn('pengguna_employee_id');
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->dropForeign(['pengguna_employee_id']);
            $table->dropColumn('pengguna_employee_id');
        });

        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->dropForeign(['pengguna_employee_id']);
            $table->dropColumn('pengguna_employee_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });

        Schema::dropIfExists('employees');
    }
};
