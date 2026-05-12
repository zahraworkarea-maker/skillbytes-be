<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseSubmissionRequest;
use App\Http\Resources\CaseSubmissionResource;
use App\Models\CaseSubmission;
use App\Models\PblCase;
use App\Models\UserCaseProgress;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="PBL Submissions",
 *     description="PBL case submissions (file upload)"
 * )
 */
class CaseSubmissionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/pbl-submissions",
     *     operationId="submitCaseAnswer",
     *     tags={"PBL Submissions"},
     *     summary="Submit answer for a PBL case (File upload)",
     *     description="Submit an answer to a PBL case as a file upload. Can only submit once per case.",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Case submission with file",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"case_id","submission_file"},
     *                 @OA\Property(property="case_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="submission_file",
     *                     description="Submission file (PDF, DOC, etc.)",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Answer submitted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Already submitted or case not available"
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function store(StoreCaseSubmissionRequest $request): JsonResponse
    {
        $user = auth()->user();
        $validated = $request->validated();
        
        $case = PblCase::findOrFail($validated['case_id']);

        // Check if already submitted
        $existingSubmission = CaseSubmission::where('user_id', $user->id)
            ->where('case_id', $case->id)
            ->first();

        if ($existingSubmission) {
            return response()->json([
                'message' => 'You have already submitted an answer for this case',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Create user progress if not exists
        $progress = UserCaseProgress::firstOrCreate(
            ['user_id' => $user->id, 'case_id' => $case->id],
            ['started_at' => Carbon::now()]
        );

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('submission_file')) {
            $file = $request->file('submission_file');
            $filename = time() . '_' . $user->id . '_' . \Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            $filePath = Storage::disk('public')
                ->putFileAs('submissions/pbl', $file, $filename);
        }

        // Create submission
        $submission = CaseSubmission::create([
            'user_id' => $user->id,
            'case_id' => $case->id,
            'submission_file' => $filePath,
            'submitted_at' => Carbon::now(),
        ]);

        // Update progress as completed
        $progress->update(['completed_at' => Carbon::now()]);

        return response()->json([
            'message' => 'Answer submitted successfully',
            'data' => new CaseSubmissionResource($submission),
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/pbl-submissions",
     *     operationId="getUserSubmissions",
     *     tags={"User - Case Submissions"},
     *     summary="Get user's submissions",
     *     description="Retrieve all submissions made by the current user",
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user's submissions"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function getUserSubmissions(): JsonResponse
    {
        $user = auth()->user();
        $submissions = $user->submissions()->with('pblCase')->paginate(15);

        return response()->json(CaseSubmissionResource::collection($submissions));
    }

    /**
     * @OA\Get(
     *     path="/api/pbl-submissions/{id}",
     *     operationId="getSubmission",
     *     tags={"User - Case Submissions"},
     *     summary="Get submission details",
     *     description="Get details of a specific submission",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Submission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Submission details"
     *     ),
     *     @OA\Response(response=404, description="Submission not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function show(CaseSubmission $submission): JsonResponse
    {
        // Ensure user can only view their own submissions
        if ($submission->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json(new CaseSubmissionResource($submission->load('pblCase')));
    }
}
