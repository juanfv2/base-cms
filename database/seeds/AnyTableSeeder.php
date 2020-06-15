<?php

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
            'auth/auth_permissions',
            'auth/auth_roles_has_permissions',
            'auth/auth_users_has_roles',
            'auth/auth_x_files',

            'oauth/oauth_clients',
            'oauth/oauth_personal_access_clients',

            'country/countries',
            'country/regions',
            'country/cities-194-el-salvador',

            // entities

            // 'accounts',

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

        $tableN = explode('/', $table)[1];
        $tableN = explode('-', $tableN)[0];

        ini_set('memory_limit', '-1');

        $json = File::get("database/data/{$table}.json");
        $data = json_decode($json, true);
        DB::table($tableN)->insert($data);
    }
}
