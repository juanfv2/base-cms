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
            $table->boolean('userCanDownload')->default(1);
            $table->string('phoneNumber')->nullable();
            $table->string('photoUrl')->nullable()->default('');

            $table->string('uid')->nullable();

            $table->foreignId('role_id')->constrained('auth_roles')->onCascade('no action');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->constrained()->onDelete('cascade');

            $table->text('api_token')->nullable();
            $table->string('remember_token')->nullable(); // aqui "$table->rememberToken();" >>> nooo!!!
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
