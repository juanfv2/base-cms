<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('icon')->default('');
            $table->string('name');
            $table->string('urlBackEnd');
            $table->string('urlFrontEnd');
            $table->boolean('isSection')->default(0);
            $table->boolean('isVisible')->default(0);
            $table->integer('permission_id')->default(0);
            $table->integer('orderInMenu')->default(0);

            $table->integer('createdBy')->nullable();
            $table->integer('updatedBy')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_permissions');
    }
}
