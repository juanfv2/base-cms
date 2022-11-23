<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->truncateTables([
            // warning: si usa passport
            // 'oauth_clients',
            // 'oauth_personal_access_clients',

            'auth_users_has_roles',
            'auth_roles_has_permissions',

            'auth_users',
            'auth_people',
            'auth_accounts',
            'auth_roles',
            'auth_permissions',

            // entities

            // 'accounts',

            // entities
        ]);
        $this->call([
            AnyTableSeeder::class,
        ]);
    }

    public function truncateTables(array $tables)
    {
        // sql server
        // foreach ($tables as $table) {
        //     DB::statement('DELETE FROM ' . $table);
        //     DB::statement("DBCC CHECKIDENT ('" . Config::get('database.connections.mysql.database') . ".dbo." . $table . "',RESEED, 0)");
        // }

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();
    }
}
