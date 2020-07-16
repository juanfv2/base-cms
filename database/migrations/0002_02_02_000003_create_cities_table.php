<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            $table->bigInteger('region_id')->unsigned();
            $table->bigInteger('country_id')->unsigned();

            $table->index(['country_id', 'region_id', 'name']);

            $table->foreign('country_id')
                ->references('id')
                ->on('countries');

            $table->foreign('region_id')
                ->references('id')
                ->on('regions');
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
        Schema::dropIfExists('cities');
        Schema::enableForeignKeyConstraints();
    }
}
