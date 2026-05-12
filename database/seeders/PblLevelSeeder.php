<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PblLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            'Beginner',
            'Intermediate',
            'Advanced',
            'Expert',
            'Master',
        ];

        foreach ($levels as $level) {
            DB::table('pbl_levels')->updateOrInsert(
                ['name' => $level],
                ['name' => $level, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
