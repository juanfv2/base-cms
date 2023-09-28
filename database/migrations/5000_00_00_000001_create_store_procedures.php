<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $default_prefix = config('base-cms.default_prefix');

        $sql = [
            // '00-01-store_procedures.sql',
            '00-02-views.sql',
            // '00-03-permissions.sql',
            // '00-03-sub-permissions.sql',
            '00-04-updates.sql',
        ];

        foreach ($sql as $key) {

            $q = database_path("migrations/sql-files/{$default_prefix}/$key");
            $qq = File::exists($q);

            logger(__FILE__.':'.__LINE__.' $q-1 ', [$q, $qq]);

            if ($qq) {
                $qString = File::get($q);
                logger(__FILE__.':'.__LINE__.' $q-2 ', [$q]);
                DB::unprepared($qString);
            }
        }
    }
};
