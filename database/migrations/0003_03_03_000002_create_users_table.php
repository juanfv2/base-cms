<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('disabled')->default(0);
            $table->string('phoneNumber')->nullable();

            $table->string('uid')->nullable();

            $table->foreignId('role_id')->constrained('auth_roles')->onCascade('no action');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('cascade');

            $table->text('api_token')->nullable();
            $table->string('remember_token')->nullable(); // aquí "$table->rememberToken();" >>> nooo!!!
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('auth_users');
        Schema::enableForeignKeyConstraints();
    }
}
