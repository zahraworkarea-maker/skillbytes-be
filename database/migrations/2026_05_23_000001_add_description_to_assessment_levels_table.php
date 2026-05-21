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
            // Add description column if it doesn't exist
            if (!Schema::hasColumn('assessment_levels', 'description')) {
                $table->text('description')->nullable()->comment('Detailed description of what this assessment level entails')->after('level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_levels', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_levels', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
