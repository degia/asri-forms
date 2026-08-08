<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->after('name');
            $table->string('department')->nullable()->after('nik');
            $table->string('business_unit')->nullable()->after('department');
            $table->string('site')->nullable()->after('business_unit');
            $table->string('no_telepon', 20)->nullable()->after('site');
            $table->string('theme_preference', 10)->default('light')->after('no_telepon');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'department',
                'business_unit',
                'site',
                'no_telepon',
                'theme_preference',
            ]);
        });
    }
};
