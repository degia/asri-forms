<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directorates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 50)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('divisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->timestamps();

            $table->unique(['directorate_id', 'name']);
        });

        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('divisi_id')->constrained('divisis')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->timestamps();

            $table->unique(['divisi_id', 'name']);
        });

        Schema::create('sub_departements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->timestamps();

            $table->unique(['departement_id', 'name']);
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 50)->nullable()->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('directorate_id')->nullable()->after('site')->constrained('directorates')->nullOnDelete();
            $table->foreignId('divisi_id')->nullable()->after('directorate_id')->constrained('divisis')->nullOnDelete();
            $table->foreignId('departement_id')->nullable()->after('divisi_id')->constrained('departements')->nullOnDelete();
            $table->foreignId('sub_departement_id')->nullable()->after('departement_id')->constrained('sub_departements')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('sub_departement_id')->constrained('positions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['directorate_id']);
            $table->dropForeign(['divisi_id']);
            $table->dropForeign(['departement_id']);
            $table->dropForeign(['sub_departement_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn(['directorate_id', 'divisi_id', 'departement_id', 'sub_departement_id', 'position_id']);
        });

        Schema::dropIfExists('positions');
        Schema::dropIfExists('sub_departements');
        Schema::dropIfExists('departements');
        Schema::dropIfExists('divisis');
        Schema::dropIfExists('directorates');
    }
};
