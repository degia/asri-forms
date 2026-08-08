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
        Schema::create('form_pengembalian', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_form')->unique();
            $table->foreignId('teknisi_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_pengembalian')->nullable();
            $table->string('kondisi')->nullable();
            $table->string('kelengkapan')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('form_pengembalian_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_pengembalian_id')->constrained('form_pengembalian')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_pengembalian_items');
        Schema::dropIfExists('form_pengembalian');
    }
};
