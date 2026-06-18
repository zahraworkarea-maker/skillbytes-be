<?php
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Models\Option;
use App\Services\AnswerService;

$attempt = AssessmentAttempt::where('status', 'IN_PROGRESS')->first();
if (!$attempt) {
    echo 'No attempt in progress';
    exit;
}

$questions = $attempt->assessment->questions;
$answers = [];
foreach ($questions as $q) {
    $option = $q->options->first();
    if ($option) {
        $answers[] = [
            'question_id' => $q->id,
            'selected_option_id' => $option->id
        ];
    }
}

$service = app(AnswerService::class);
try {
    $result = $service->submitAnswersBulk($attempt, $answers);
    echo json_encode($result);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
