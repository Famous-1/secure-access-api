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
        // Update the enum to include 'acknowledged' status
        DB::statement("ALTER TABLE complaints MODIFY COLUMN status ENUM('pending', 'acknowledged', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE complaints MODIFY COLUMN status ENUM('pending', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'pending'");
    }
};

