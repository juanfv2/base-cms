<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_x_files');
    }
};
