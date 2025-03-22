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
        // Update url field in users table if exists
        if (Schema::hasColumn('users', 'url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('url')->nullable()->change(); // Ensure url is nullable text
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert as this is just ensuring the url field is of text type
    }
};
