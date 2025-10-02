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
        // First, update the enum to include new statuses without changing existing data
        DB::statement("ALTER TABLE visitor_codes MODIFY COLUMN status ENUM('pending', 'active', 'used', 'complete', 'expired', 'cancelled') NOT NULL DEFAULT 'active'");
        
        // Then update existing 'used' status to 'complete'
        DB::table('visitor_codes')->where('status', 'used')->update(['status' => 'complete']);
        
        // Finally, update the enum to remove 'used' and set default to 'pending'
        DB::statement("ALTER TABLE visitor_codes MODIFY COLUMN status ENUM('pending', 'active', 'complete', 'expired', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        DB::statement("ALTER TABLE visitor_codes MODIFY COLUMN status ENUM('active', 'used', 'expired', 'cancelled') NOT NULL DEFAULT 'active'");
        
        // Convert 'complete' back to 'used'
        DB::table('visitor_codes')->where('status', 'complete')->update(['status' => 'used']);
    }
};
