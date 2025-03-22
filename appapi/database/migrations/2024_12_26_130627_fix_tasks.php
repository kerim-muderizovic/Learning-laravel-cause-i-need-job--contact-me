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
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                // Make sure we're only modifying columns that exist
                if (Schema::hasColumn('tasks', 'description')) {
                    $table->string('description')->nullable()->change();
                }
                
                if (Schema::hasColumn('tasks', 'progress')) {
                    $table->string('progress')->nullable()->change(); // Changed to string as in later migration
                }
                
                if (Schema::hasColumn('tasks', 'completed')) {
                    $table->boolean('completed')->default(false)->nullable()->change();
                }
                
                if (Schema::hasColumn('tasks', 'priority')) {
                    $table->string('priority')->nullable()->change(); // Changed to string from enum
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal as we're just making fields nullable
    }
};
