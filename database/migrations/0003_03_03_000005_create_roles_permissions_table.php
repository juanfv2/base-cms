<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_roles_has_permissions', function (Blueprint $table) {

            $table->bigInteger('role_id')->unsigned();
            $table->bigInteger('permission_id')->unsigned();

            $table->primary(['role_id', 'permission_id']);

            $table->foreign('role_id')
                ->references('id')
                ->on('auth_roles')
                ->onDelete('cascade');

            $table->foreign('permission_id')
                ->references('id')
                ->on('auth_permissions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_roles_has_permissions');
    }
}
