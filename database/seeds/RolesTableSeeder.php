<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $table = 'auth_roles';
        // sqlserver DB::statement('set identity_insert ' . $table . ' on');
        // DB::table($table)->insert([
        //     ['id' => 1, 'name' => 'Administrador', 'description' => 'Administrador'],
        //     ['id' => 2, 'name' => 'Sub Administrador', 'description' => 'Sub Administrador'],
        //     ['id' => 3, 'name' => 'Monitor', 'description' => 'Monitoreo de transacciones'],
        //     ['id' => 4, 'name' => 'Cliente Comercio', 'description' => 'Usuario del sistema'],
        //     ['id' => 5, 'name' => 'Cliente Consumidor Final', 'description' => 'Usuario del sistema'],
        // ]);
        // sqlserver DB::statement('set identity_insert ' . $table . ' off');

        $table = 'auth_roles';
        $json = File::get("database/data/auth/{$table}.json");
        $data = json_decode($json, true);
        DB::table($table)->insert($data);
    }
}
