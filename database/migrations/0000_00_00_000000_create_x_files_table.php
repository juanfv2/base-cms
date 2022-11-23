<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXFilesTable extends Migration
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
            $table->bigInteger('entity_id');
            $table->string('field');
            $table->string('name');
            $table->string('nameOriginal');
            $table->string('publicPath')->default('');
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
