<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->shift(7);
    }

    public function down(): void
    {
        $this->shift(-7);
    }

    private function shift(int $hours): void
    {
        $tables = DB::select('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');

        foreach ($tables as $t) {
            $cols = DB::select(
                'SELECT COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                 AND DATA_TYPE IN ("datetime", "timestamp")',
                [$t->TABLE_NAME]
            );

            foreach ($cols as $c) {
                DB::table($t->TABLE_NAME)
                    ->whereNotNull($c->COLUMN_NAME)
                    ->update([$c->COLUMN_NAME => DB::raw('DATE_ADD(' . $c->COLUMN_NAME . ', INTERVAL ' . $hours . ' HOUR)')]);
            }
        }
    }
};
