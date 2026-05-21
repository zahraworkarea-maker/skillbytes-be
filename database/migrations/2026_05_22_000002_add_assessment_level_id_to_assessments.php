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

        Schema::table('assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('assessments', 'assessment_level_id')) {
                $table->foreignId('assessment_level_id')->nullable()->after('time_limit')
                    ->constrained('assessment_levels')
                    ->nullOnDelete();
            }
        });

        // Optionally backfill assessment_level_id by matching level numbers if possible
        if (Schema::hasTable('assessment_levels') && Schema::hasColumn('assessment_levels', 'level_number')) {
            // This assumes assessments.level contains numeric level values
            try {
                DB::statement('UPDATE assessments SET assessment_level_id = al.id FROM assessment_levels al WHERE assessments.level = al.level_number');
            } catch (\Throwable $e) {
                // ignore if mismatch
            }
        }
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
            if (Schema::hasColumn('assessments', 'assessment_level_id')) {
                try {
                    $table->dropForeign(['assessment_level_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('assessment_level_id');
            }
        });
    }
};
