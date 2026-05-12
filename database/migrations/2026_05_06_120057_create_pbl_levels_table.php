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
        // Check if table already exists from old migration
        if (!Schema::hasTable('pbl_levels')) {
            Schema::create('pbl_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
        
        // Add any missing columns if upgrading from old table
        Schema::table('pbl_levels', function (Blueprint $table) {
            // Ensure name column exists and has correct attributes
            if (!Schema::hasColumn('pbl_levels', 'name')) {
                $table->string('name')->unique();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table if it was created by older migration
        // Just remove columns added by this migration if needed
    }
};
