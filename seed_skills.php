<?php

$levels = App\Models\AssessmentLevel::all();
foreach($levels as $level) {
    $skill = App\Models\Skill::firstOrCreate(['name' => $level->description ?: 'Level '.$level->level_number]);
    $assessments = App\Models\Assessment::where('assessment_level_id', $level->id)->get();
    foreach($assessments as $ass) {
        $questions = App\Models\Question::where('assessment_id', $ass->id)->get();
        foreach($questions as $q) {
            $q->skills()->syncWithoutDetaching([$skill->id]);
        }
    }
}
echo "Skills seeded from assessment levels.\n";
