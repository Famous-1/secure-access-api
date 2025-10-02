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
        Schema::table('visitor_codes', function (Blueprint $table) {
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_codes', function (Blueprint $table) {
            $table->dropColumn(['time_in', 'time_out']);
        });
    }
};
