<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $domainEmail = 'demo.com';
        $table = 'auth_users';
        DB::table($table)->insert([
            ['id' => 1, 'name' => 'Admin Admin',     'email' => 'admin@' . $domainEmail,     'password' => bcrypt('123456'), 'role_id' => 1],
            ['id' => 2, 'name' => 'Sub Admin Admin', 'email' => 'subadmin@' . $domainEmail,  'password' => bcrypt('123456'), 'role_id' => 2],

            ['id' => 3, 'name' => 'juanfv2',         'email' => 'juanfv1@gmail.com',         'password' => bcrypt('123456'), 'role_id' => 3],
            // ['id' => 4, 'name' => 'pablo',         'email' => 'pablo@gmail.com',           'password' => bcrypt('123456'), 'role_id' => 3],
        ]);

        $table = 'auth_people';
        DB::table($table)->insert([
            ['id' => 1, 'firstName' => 'Admin Admin', 'lastName' => 'Admin Admin', 'email' => 'admin@' . $domainEmail],
            ['id' => 2, 'firstName' => 'Sub Admin',   'lastName' => 'Sub Admin',   'email' => 'subadmin@' . $domainEmail],
        ]);

        $table = 'auth_accounts';
        DB::table($table)->insert([
            ['id' => 1, 'firstName' => 'juanfv2', 'lastName' => 'Demo demo', 'email' => 'juanfv1@gmail.com'],
            // ['id' => 2, 'firstName' => 'Demo', 'lastName' => 'user user', 'email' => 'demo@gmail.com'],
        ]);
    }
}
