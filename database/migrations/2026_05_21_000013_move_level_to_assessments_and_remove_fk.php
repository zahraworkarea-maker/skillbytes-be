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
        if (! Schema::hasTable('assessments')) {
            return;
        }

        // Add integer `level` column to assessments
        Schema::table('assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('assessments', 'level')) {
                $table->unsignedSmallInteger('level')->nullable()->after('time_limit');
            }
        });

        // Backfill level from assessment_levels if available
        if (Schema::hasTable('assessment_levels') && Schema::hasColumn('assessment_levels', 'level')) {
            DB::statement('UPDATE assessments SET level = (SELECT level FROM assessment_levels WHERE assessment_levels.id = assessments.assessment_level_id) WHERE assessment_level_id IS NOT NULL');
        }

        // Remove foreign key and column assessment_level_id
        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'assessment_level_id')) {
                // drop foreign key if exists
                try {
                    $table->dropForeign(['assessment_level_id']);
                } catch (\Throwable $e) {
                    // ignore if constraint name differs or not exists
                }

                $table->dropColumn('assessment_level_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('assessments')) {
            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('assessments', 'assessment_level_id')) {
                $table->foreignId('assessment_level_id')->nullable()->after('time_limit')
                    ->constrained('assessment_levels')
                    ->nullOnDelete();
            }

            if (Schema::hasColumn('assessments', 'level')) {
                $table->dropColumn('level');
            }
        });
    }
};
