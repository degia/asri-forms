<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_perawatan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_form')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pengguna_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('asset_id')->constrained('assets');
            $table->enum('kondisi_akhir', ['good_normal', 'caution_poor']);
            $table->text('kondisi_akhir_notes')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'diketahui', 'disetujui', 'selesai', 'revisi'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('form_perawatan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_perawatan_id')->constrained('form_perawatan')->cascadeOnDelete();
            $table->foreignId('template_item_id')->nullable()->constrained('checklist_template_items')->nullOnDelete();
            $table->enum('category', ['hardware', 'aplikasi', 'operating_system']);
            $table->string('name');
            $table->enum('status', ['baik', 'tidak_baik'])->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_perawatan_items');
        Schema::dropIfExists('form_perawatan');
    }
};
