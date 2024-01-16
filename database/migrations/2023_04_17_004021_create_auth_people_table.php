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
        Schema::create('auth_people', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('cell_phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();

            $table->foreignId('user_id')->constrained('auth_users')->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_people');
    }
};
