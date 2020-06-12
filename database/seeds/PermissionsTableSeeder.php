<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tables = [
            'auth_permissions',
            'auth_roles_has_permissions',
            'auth_users_has_roles',

            'oauth_clients',
            'oauth_personal_access_clients'
        ];

        foreach ($tables as $table) {
            // sqlserver: --> DB::statement('set identity_insert ' . $table . ' on');
            $json = File::get("database/data/auth/{$table}.json");
            $data = json_decode($json, true);
            DB::table($table)->insert($data);
            // sqlserver: --> DB::statement('set identity_insert ' . $table . ' off');
        }
    }
}
