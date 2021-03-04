<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_people', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('phone')->nullable();
            $table->string('cellPhone')->nullable();
            $table->date('birthDate')->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();

            $table->string('email')->unique();
            $table->foreign('email')->references('email')->on('auth_users')->onDelete('cascade');

            $table->foreignId('country_id')->constrained()->nullable();
            $table->foreignId('region_id')->constrained()->nullable();
            $table->foreignId('city_id')->constrained()->nullable();

            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
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
        Schema::dropIfExists('auth_people');
    }
}
