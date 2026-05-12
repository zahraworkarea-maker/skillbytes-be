<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbl_cases', function (Blueprint $table) {
            if (Schema::hasColumn('pbl_cases', 'level_id')) {
                // Drop foreign key first
                try {
                    $table->dropForeign(['level_id']);
                } catch (\Exception $e) {
                    // Key might not exist
                }
                
                // Rename column
                $table->renameColumn('level_id', 'pbl_level_id');
                
                // Re-add foreign key with new name
                $table->foreign('pbl_level_id')->references('id')->on('pbl_levels')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pbl_cases', function (Blueprint $table) {
            if (Schema::hasColumn('pbl_cases', 'pbl_level_id')) {
                try {
                    $table->dropForeign(['pbl_level_id']);
                } catch (\Exception $e) {
                    // Key might not exist
                }
                
                $table->renameColumn('pbl_level_id', 'level_id');
                
                try {
                    $table->foreign('level_id')->references('id')->on('pbl_levels')->onDelete('cascade');
                } catch (\Exception $e) {
                    // Fallback
                }
            }
        });
    }
};
