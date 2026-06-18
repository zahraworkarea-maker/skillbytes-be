<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Models\CaseSubmission;
use App\Models\AssessmentLevel;
use App\Models\User;
use App\Models\UserSkillMastery;
use App\Models\Skill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KnowledgeTracingService
{
    private float $assessmentWeight;
    private float $pblWeight;
    private float $threshold;

    public function __construct()
    {
        $this->assessmentWeight = config('knowledge_tracing.weights.assessment', 0.70);
        $this->pblWeight = config('knowledge_tracing.weights.pbl', 0.30);
        $this->threshold = config('knowledge_tracing.threshold', 60.0);
    }

    /**
     * Get mastery data for a specific student based on DKT UserSkillMastery
     */
    public function getStudentMastery(int $userId): array
    {
        $skills = Skill::orderBy('name')->get();
        $masteryRecords = UserSkillMastery::where('user_id', $userId)
            ->pluck('mastery_probability', 'skill_id')
            ->toArray();

        $masteryList = [];
        $needsAttention = [];

        foreach ($skills as $skill) {
            $probability = isset($masteryRecords[$skill->id]) ? (float) $masteryRecords[$skill->id] : 0.0;
            // Convert probability (0.0 - 1.0) to percentage (0 - 100)
            $masteryScore = round($probability * 100, 2);

            $item = [
                'material_id' => $skill->id,
                'material_name' => $skill->name,
                'mastery_score' => $masteryScore,
                'probability' => $probability
            ];

            $masteryList[] = $item;

            // If mastery is below threshold, add to needs attention
            if ($masteryScore < $this->threshold) {
                $needsAttention[] = $item;
            }
        }

        return [
            'mastery_list' => $masteryList,
            'needs_attention' => $needsAttention,
        ];
    }

    /**
     * Get class average mastery from UserSkillMastery
     */
    public function getClassMastery(): array
    {
        $skills = Skill::orderBy('name')->get();
        $students = User::where('role', 'siswa')->get();
        $validStudentsCount = max(1, count($students));

        // Get all mastery records grouped by skill and user
        $allMastery = UserSkillMastery::get();
        
        $masteryMap = []; // [userId][skillId] = probability
        foreach ($allMastery as $record) {
            $masteryMap[$record->user_id][$record->skill_id] = (float) $record->mastery_probability;
        }

        $classAverages = [];
        $studentsNeedingAttention = [];

        foreach ($skills as $skill) {
            $totalProbabilityForSkill = 0;

            foreach ($students as $student) {
                $prob = $masteryMap[$student->id][$skill->id] ?? 0.0;
                $totalProbabilityForSkill += $prob;

                $masteryScore = round($prob * 100, 2);

                if ($masteryScore < $this->threshold) {
                    if (!isset($studentsNeedingAttention[$student->id])) {
                        $studentsNeedingAttention[$student->id] = [
                            'user_id' => $student->id,
                            'name' => $student->name,
                            'materials' => []
                        ];
                    }
                    $studentsNeedingAttention[$student->id]['materials'][] = [
                        'material_id' => $skill->id,
                        'material_name' => $skill->name,
                        'mastery_score' => $masteryScore
                    ];
                }
            }

            $avgProb = $totalProbabilityForSkill / $validStudentsCount;
            $classAverages[] = [
                'material_id' => $skill->id,
                'material_name' => $skill->name,
                'average_mastery' => round($avgProb * 100, 2),
            ];
        }

        return [
            'class_averages' => $classAverages,
            'students_needing_attention' => array_values($studentsNeedingAttention),
        ];
    }

    /**
     * Get learning recommendations based on weaknesses
     */
    public function getRecommendations(int $userId): array
    {
        $masteryData = $this->getStudentMastery($userId);
        
        // Sort needs_attention materials by mastery score (lowest first)
        $needsAttention = collect($masteryData['needs_attention'])
            ->sortBy('mastery_score')
            ->values()
            ->all();

        $recommendations = [];

        foreach ($needsAttention as $item) {
            $levelNo = $item['material_id'];
            
            // Get all lessons for this level number
            $lessons = \App\Models\Lesson::whereHas('level', function($q) use ($levelNo) {
                $q->where('level_number', $levelNo);
            })->get();
            
            // Check if user has completed these lessons
            $userLessons = \App\Models\UserLesson::where('user_id', $userId)
                ->where('completed', true)
                ->pluck('lesson_id')
                ->toArray();
                
            $recommendedLessons = [];
            foreach ($lessons as $lesson) {
                if (!in_array($lesson->id, $userLessons)) {
                    $recommendedLessons[] = [
                        'lesson_id' => $lesson->id,
                        'slug' => $lesson->slug,
                        'title' => $lesson->title,
                        'description' => $lesson->description,
                    ];
                }
            }
            
            // Only add to recommendations if there are actually lessons to recommend
            if (count($recommendedLessons) > 0) {
                $recommendations[] = [
                    'level_number' => $levelNo,
                    'level_name' => $item['material_name'],
                    'mastery_score' => $item['mastery_score'],
                    'recommended_lessons' => $recommendedLessons
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Get student mastery history over time (cumulative)
     */
    public function getMasteryHistory(int $userId): array
    {
        // Get all assessments for this user
        $attempts = AssessmentAttempt::where('user_id', $userId)
            ->where('status', 'completed')
            ->join('assessments', 'assessment_attempts.assessment_id', '=', 'assessments.id')
            ->select('assessment_attempts.id', 'assessments.assessment_level_id as level_number', 'assessment_attempts.score', 'assessment_attempts.created_at', \DB::raw("'assessment' as type"))
            ->get();

        // Get all PBL submissions for this user
        $submissions = CaseSubmission::where('user_id', $userId)
            ->whereNotNull('score')
            ->join('pbl_cases', 'case_submissions.case_id', '=', 'pbl_cases.id')
            ->select('case_submissions.id', 'pbl_cases.pbl_level_id as level_number', 'case_submissions.score', 'case_submissions.created_at', \DB::raw("'pbl' as type"))
            ->get();

        // Combine and sort chronologically
        $allActivities = $attempts->concat($submissions)->sortBy('created_at')->values();

        $currentScores = []; // Keep track of best score so far per level
        $overallMasteryPoints = [];

        $assessmentLevels = AssessmentLevel::orderBy('level_number')->get();
        $totalLevelsCount = max($assessmentLevels->count(), 1);

        foreach ($allActivities as $activity) {
            $levelNo = $activity->level_number;
            $type = $activity->type;
            $score = (float) $activity->score;

            if (!isset($currentScores[$levelNo])) {
                $currentScores[$levelNo] = ['assessment' => 0.0, 'pbl' => 0.0];
            }

            // Update best score if it's higher
            if ($score > $currentScores[$levelNo][$type]) {
                $currentScores[$levelNo][$type] = $score;
            }

            // Calculate overall mastery at this point in time
            $totalMastery = 0;
            foreach ($assessmentLevels as $level) {
                $lNo = $level->level_number;
                $ass = $currentScores[$lNo]['assessment'] ?? 0.0;
                $pbl = $currentScores[$lNo]['pbl'] ?? 0.0;
                $levelMastery = ($ass * $this->assessmentWeight) + ($pbl * $this->pblWeight);
                $totalMastery += $levelMastery;
            }

            $averageOverallMastery = $totalMastery / $totalLevelsCount;
            $dateStr = $activity->created_at->format('Y-m-d');
            
            // Keep the latest for each day
            $overallMasteryPoints[$dateStr] = [
                'date' => $dateStr,
                'overall_mastery' => round($averageOverallMastery, 2)
            ];
        }

        return array_values($overallMasteryPoints);
    }

    /**
     * Get students at risk with specific level filtering and severity
     */
    public function getStudentsAtRisk(?int $levelId = null): array
    {
        $classData = $this->getClassMastery();
        $needsAttention = $classData['students_needing_attention'];
        
        $atRisk = [];
        
        foreach ($needsAttention as $student) {
            $filteredMaterials = [];
            foreach ($student['materials'] as $material) {
                // If filtering by level
                if ($levelId && $material['material_id'] != $levelId) {
                    continue;
                }
                
                $score = $material['mastery_score'];
                $severity = 'low'; // low risk
                
                if ($score < 40) {
                    $severity = 'high'; // Very poor performance
                } elseif ($score < $this->threshold) {
                    $severity = 'medium'; // Below threshold but not disastrous
                }
                
                $material['severity'] = $severity;
                $filteredMaterials[] = $material;
            }
            
            // Only include student if they still have materials needing attention after filter
            if (count($filteredMaterials) > 0) {
                $student['materials'] = $filteredMaterials;
                $atRisk[] = $student;
            }
        }
        
        return $atRisk;
    }
}
