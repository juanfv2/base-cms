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
        Schema::create('item_fields', function (Blueprint $table) {
            $table->id();
            $table->string('alias')->default('');
            $table->string('name')->default('');
            $table->string('label')->default('');
            $table->string('field')->default('');
            $table->string('type')->default('');
            $table->boolean('allowSearch')->default(true);
            $table->boolean('allowExport')->default(true);
            $table->boolean('allowImport')->default(true);
            $table->boolean('allowInList')->default(true);
            $table->boolean('hidden')->default(false);
            $table->boolean('sorting')->default(true);
            $table->boolean('fixed')->default(false);
            $table->integer('index')->default(0);
            $table->string('table')->default('');
            $table->string('model')->default('');
            $table->text('extra')->nullable();
            $table->string('allowNull')->default('');
            $table->string('key')->default('');
            $table->string('defaultValue')->default('');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_fields');
    }
};
