<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');

            $table->integer('createdBy')->nullable();
            $table->integer('updatedBy')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /*
    id int NOT NULL IDENTITY(1,1),
    name varchar(255) NOT NULL,
    email varchar(255) NULL,
    password varchar(255) NOT NULL,
    photoUrl varchar(255) NULL DEFAULT (NULL),
    disabled int NOT NULL DEFAULT ('1'),
    group_id varchar(100) NULL DEFAULT (NULL),
    rememberToken varchar(100) NULL DEFAULT (NULL),
    phoneNumber varchar(45) NULL DEFAULT (NULL),
    company_id int NULL,
    role_id int NULL DEFAULT (NULL),
    createdBy int NULL DEFAULT (NULL),
    updatedBy int NULL DEFAULT (NULL),
    created_at datetime NULL DEFAULT (NULL),
    updated_at datetime NULL DEFAULT (NULL),
    deleted_at datetime NULL DEFAULT (NULL) */

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_roles');
    }
}
