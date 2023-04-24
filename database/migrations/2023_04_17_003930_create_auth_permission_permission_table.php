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
        Schema::create('auth_permission_permission', function (Blueprint $table) {
            $table->foreignId('parent_id')->constrained('auth_permissions');
            $table->foreignId('child_id')->constrained('auth_permissions');

            $table->primary(['parent_id', 'child_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_permission_permission');
    }
};
