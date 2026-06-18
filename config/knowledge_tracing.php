<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Knowledge Tracing Weights
    |--------------------------------------------------------------------------
    |
    | Define the weight distribution for calculating the Mastery Score.
    | The sum of all weights should ideally be 1.0 (100%).
    | Formula: Mastery = (Assessment × assessment_weight) + (PBL × pbl_weight)
    |
    */
    'weights' => [
        'assessment' => 0.70,
        'pbl' => 0.30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mastery Threshold
    |--------------------------------------------------------------------------
    |
    | Define the minimum score required to be considered as "Mastered".
    | Scores below this threshold will be flagged as "needs attention".
    |
    */
    'threshold' => 60.0,
];
