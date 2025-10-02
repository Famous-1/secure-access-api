<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update the enum to include all current and new values
        DB::statement("ALTER TABLE users MODIFY COLUMN usertype ENUM('admin', 'installer', 'user', 'vendor', 'resident', 'maintainer') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE users MODIFY COLUMN usertype ENUM('admin', 'installer', 'user', 'vendor') NOT NULL DEFAULT 'user'");
    }
};
