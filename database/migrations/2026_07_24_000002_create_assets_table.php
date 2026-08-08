<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('kategori'); // Laptop, Desktop, Printer, etc.
            $table->string('brand');
            $table->string('tipe');
            $table->string('nama_perangkat');
            $table->string('no_serial')->nullable();
            $table->string('no_asset')->unique();
            $table->string('qr_code')->nullable();
            $table->string('status')->default('active'); // active, inactive, disposed
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
