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
        if (! Schema::hasTable('assessment_levels')) {
            Schema::create('assessment_levels', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('level_number')->unique();
                $table->string('name')->nullable();
                $table->timestamps();
            });
            return;
        }

        // Ensure level_number column exists
        Schema::table('assessment_levels', function (Blueprint $table) {
            if (! Schema::hasColumn('assessment_levels', 'level_number')) {
                $table->unsignedSmallInteger('level_number')->nullable()->after('id');
            }
            if (! Schema::hasColumn('assessment_levels', 'name')) {
                $table->string('name')->nullable()->after('level_number');
            }
        });

        // Backfill level_number with id if empty
        DB::statement('UPDATE assessment_levels SET level_number = COALESCE(level_number, id)');

        // Remove columns not needed: slug, label, description
        Schema::table('assessment_levels', function (Blueprint $table) {
            foreach (['slug', 'label', 'description'] as $col) {
                if (Schema::hasColumn('assessment_levels', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Enforce unique constraint on level_number
        // Some DBs require index existence check; try to create unique index if not exists
        try {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS assessment_levels_level_number_unique ON assessment_levels(level_number)');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('assessment_levels')) {
            return;
        }

        Schema::table('assessment_levels', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_levels', 'level_number')) {
                $table->dropColumn('level_number');
            }
            if (! Schema::hasColumn('assessment_levels', 'slug')) {
                $table->string('slug')->nullable()->after('id');
            }
            if (! Schema::hasColumn('assessment_levels', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
        });
    }
};
