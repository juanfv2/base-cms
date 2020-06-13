<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('phone')->nullable();
            $table->string('cellPhone')->nullable();
            $table->date('birthDate')->nullable();
            $table->string('email')->unique();
            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();

            $table->integer('country_id')->unsigned()->nullable();
            $table->integer('region_id')->unsigned()->nullable();
            $table->integer('city_id')->unsigned()->nullable();

            $table->integer('createdBy')->nullable();
            $table->integer('updatedBy')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('email')
                ->references('email')
                ->on('auth_users')
                ->onDelete('cascade');

            $table->foreign('country_id')
                ->references('id')
                ->on('countries');

            $table->foreign('region_id')
                ->references('id')
                ->on('regions');

            $table->foreign('city_id')
                ->references('id')
                ->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
