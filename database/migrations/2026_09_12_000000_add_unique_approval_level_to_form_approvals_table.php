<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_approvals', function (Blueprint $table) {
            $table->unique(['approvable_type', 'approvable_id', 'approval_level'], 'form_approvals_unique_level');
        });
    }

    public function down(): void
    {
        Schema::table('form_approvals', function (Blueprint $table) {
            $table->dropUnique('form_approvals_unique_level');
        });
    }
};