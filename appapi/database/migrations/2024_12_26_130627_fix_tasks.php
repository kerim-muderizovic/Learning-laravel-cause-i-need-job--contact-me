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
        //
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('description')->nullable()->change();;
            $table->integer('progress')->default(0)->nullable()->change();;
            $table->boolean('completed')->default(false)->nullable()->change();;
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
