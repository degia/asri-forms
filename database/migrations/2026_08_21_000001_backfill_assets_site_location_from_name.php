<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE assets
            JOIN sites ON sites.id_site = UPPER(SUBSTRING_INDEX(assets.nama_perangkat, '-', 1))
            SET assets.site_location_asset = sites.id_site
            WHERE assets.site_location_asset IS NULL OR assets.site_location_asset = ''
        SQL);
    }

    public function down(): void
    {
        //
    }
};
