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
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('requireStrongPassword')->default(false);
            $table->boolean('allow_creating_accounts')->default(false);
            $table->integer('user_deletion_days')->default(30);
            $table->boolean('enable_audit_logs')->default(true);
            $table->boolean('enable_reset_password')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('requireStrongPassword');
            $table->dropColumn('allow_creating_accounts');
            $table->dropColumn('user_deletion_days');
            $table->dropColumn('enable_audit_logs');
            $table->dropColumn('enable_reset_password');
        });
    }
};
