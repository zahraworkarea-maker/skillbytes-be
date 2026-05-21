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
        Schema::table('assessment_levels', function (Blueprint $table) {
            // Drop existing columns
            if (Schema::hasColumn('assessment_levels', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('assessment_levels', 'label')) {
                $table->dropColumn('label');
            }
            
            // Add level_number column
            if (!Schema::hasColumn('assessment_levels', 'level_number')) {
                $table->unsignedTinyInteger('level_number')->unique()->comment('Numeric level (e.g., 1, 2, 3, ...)')->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_levels', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_levels', 'level_number')) {
                $table->dropColumn('level_number');
            }
            
            $table->unsignedTinyInteger('level')->unique()->comment('Numeric level (e.g., 1,2,3,...)')->after('id');
            $table->string('label')->nullable()->comment('Human readable label for the level')->after('level');
        });
    }
};
