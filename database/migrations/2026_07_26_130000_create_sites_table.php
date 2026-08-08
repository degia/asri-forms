<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->string('id_site', 3)->primary();
            $table->string('site');
            $table->string('buss', 1);
            $table->string('id_corp', 3);
            $table->string('country');
            $table->string('provincy');
            $table->string('city');
            $table->longText('address');
            $table->longText('url_maps')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
