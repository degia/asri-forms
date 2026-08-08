<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $siteMap = [
            'PIK Avenue' => 'M01',
            'Menara Jakarta' => 'A03',
            'Hublife' => 'M05',
            'ASRI Corp HQ' => 'O99',
            'ASRI Corp Treasury Tower 70' => 'O99',
        ];

        foreach ($siteMap as $oldName => $idSite) {
            $idCorp = DB::table('sites')->where('id_site', $idSite)->value('id_corp');
            DB::table('users')
                ->where('site', $oldName)
                ->update(['site' => $idSite, 'business_unit' => $idCorp]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->index('business_unit', 'users_business_unit_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_business_unit_index');
        });
    }
};
