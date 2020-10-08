<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AnyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tables = [
            'auth/auth_roles',
            'auth/auth_users',
            'auth/auth_people',
            'auth/auth_accounts',
            'auth/auth_users_has_roles',

            'country/countries',
            'country/regions',
            'country/cities-194-3224',
            // 'carpeta-a/carpeta-b/country/cities-1', // (mysql insert multiple rows maximum) ej. 5000
            // 'carpeta-a/carpeta-b/country/cities-2', // (mysql insert multiple rows maximum) ej. 5000

            // entities

            // '/entities/accounts',

            // entities
        ];
        foreach ($tables as $table) {
            $this->inserts($table);
        }
    }

    function inserts($table)
    {
        logger(__FILE__ . ':' . __LINE__ . ' "database/data/{$table}.json" ', ["database/data/{$table}.json"]);
        // Allowed memory size of 134217728 bytes exhausted (tried to allocate
        $table1 = explode('/', $table);
        $table2 = end($table1);
        $table3 = explode('-', $table2);
        $tableN = reset($table3);

        ini_set('memory_limit', '-1');

        $json = File::get("database/data/{$table}.json");
        $data = json_decode($json, true);

        DB::table($tableN)->insert($data);
    }
}
