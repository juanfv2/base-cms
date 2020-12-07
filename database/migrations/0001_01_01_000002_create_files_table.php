<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_x_files', function (Blueprint $table) {
            $table->id();
            $table->string('entity');
            $table->integer('entity_id');
            $table->string('field');
            $table->string('name');
            $table->string('nameOriginal');
            $table->string('extension', 10);
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_x_files');
    }
}
