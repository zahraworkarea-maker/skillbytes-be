<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssessmentLevel;
use App\Models\Skill;
use App\Models\Assessment;
use App\Models\Question;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = AssessmentLevel::all();
        foreach($levels as $level) {
            $skill = Skill::firstOrCreate(['name' => $level->description ?: 'Level '.$level->level_number]);
            $assessments = Assessment::where('assessment_level_id', $level->id)->get();
            foreach($assessments as $ass) {
                $questions = Question::where('assessment_id', $ass->id)->get();
                foreach($questions as $q) {
                    $q->skills()->syncWithoutDetaching([$skill->id]);
                }
            }
        }
    }
}
