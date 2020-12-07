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
        Schema::create('auth_accounts', function (Blueprint $table) {
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
            $table->foreignId('country_id')->nullable()->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('city_id')->nullable()->constrained();

            $table->bigInteger('createdBy')->nullable();
            $table->bigInteger('updatedBy')->nullable();
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
        Schema::dropIfExists('auth_accounts');
    }
}
