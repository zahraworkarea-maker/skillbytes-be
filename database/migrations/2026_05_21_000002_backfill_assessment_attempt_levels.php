<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill level for existing attempts where level is null
        DB::table('assessment_attempts')
            ->whereNull('level')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $score = isset($row->score) ? (float) $row->score : 0.0;

                    $level = 'Novice';
                    if ($score >= 71) {
                        $level = 'Advanced';
                    } elseif ($score >= 41) {
                        $level = 'Intermediate';
                    }

                    DB::table('assessment_attempts')
                        ->where('id', $row->id)
                        ->update(['level' => $level]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: do not remove historical level data on rollback
    }
};
