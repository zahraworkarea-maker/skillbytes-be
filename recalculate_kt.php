<?php

use Illuminate\Support\Facades\Artisan;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dktEngine = app(App\Services\DktEngineService::class);
$answers = App\Models\AttemptAnswer::with('attempt')->orderBy('created_at', 'asc')->get();
$total = $answers->count();
echo "Found $total answers to process.\n";

$count = 0;
foreach($answers as $ans) {
    $question = App\Models\Question::with('skills')->find($ans->question_id);
    if ($question && $ans->attempt) {
        $dktEngine->updateMastery($ans->attempt->user_id, $question, $ans->is_correct, $ans->attempt_id);
        $count++;
        if ($count % 10 == 0) {
            echo "Processed $count answers...\n";
        }
    }
}
echo "Recalculated KT for $count answers.\n";
