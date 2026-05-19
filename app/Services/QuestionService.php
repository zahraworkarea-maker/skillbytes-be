<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class QuestionService
{
    private const QUESTIONS_STORAGE_PATH = 'questions';
    private const MAX_IMAGE_SIZE = 5120; // 5MB in KB

    /**
     * Create question for assessment
     */
    public function createQuestion(Assessment $assessment, array $data): Question
    {
        $data['assessment_id'] = $assessment->id;
        $data = $this->handleImageUpload($data);
        return Question::create($data);
    }

    /**
     * Create multiple questions in bulk
     */
    public function createBulkQuestions(Assessment $assessment, array $questions): array
    {
        $createdQuestions = [];
        foreach ($questions as $questionData) {
            $questionData['assessment_id'] = $assessment->id;
            $questionData = $this->handleImageUpload($questionData);
            $createdQuestions[] = Question::create($questionData);
        }
        return $createdQuestions;
    }

    /**
     * Update question
     */
    public function updateQuestion(Question $question, array $data): Question
    {
        // Delete old image if new image is being uploaded
        if (isset($data['image']) && $data['image']) {
            if ($question->image_path) {
                Storage::disk('public')->delete($question->image_path);
            }
            $data = $this->handleImageUpload($data);
        }
        
        $question->update($data);
        return $question;
    }

    /**
     * Delete question
     */
    public function deleteQuestion(Question $question): bool
    {
        // Delete image if exists
        if ($question->image_path) {
            Storage::disk('public')->delete($question->image_path);
        }
        
        return $question->delete();
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload(array $data): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $imagePath = $data['image']->store(self::QUESTIONS_STORAGE_PATH, 'public');
            $data['image_path'] = $imagePath;
        }
        
        unset($data['image']);
        return $data;
    }

    /**
     * Get question with options
     */
    public function getQuestionWithOptions(int $questionId): ?Question
    {
        return Question::with('options')->find($questionId);
    }

    /**
     * Get assessment questions
     */
    public function getAssessmentQuestions(Assessment $assessment): Collection
    {
        return $assessment->questions()->with('options')->get();
    }

    /**
     * Validate minimum one correct answer exists
     */
    public function hasCorrectAnswer(Question $question): bool
    {
        return $question->options()->where('is_correct', true)->exists();
    }
}

