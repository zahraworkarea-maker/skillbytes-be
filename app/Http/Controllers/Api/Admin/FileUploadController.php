<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadFileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="File Management",
 *     description="File management (Admin/Guru only)"
 * )
 */
class FileUploadController extends Controller
{
    /**
     * @OA\Post(
     *     path="/upload-file",
     *     operationId="uploadFile",
     *     tags={"File Management"},
     *     summary="Upload a file (Admin/Guru only)",
     *     description="Upload any file format for lessons/materi (resume, document, etc.). Only accessible to Admin and Guru roles. Max size: 100MB",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="File to upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     description="File to upload (any format allowed)",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File uploaded successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="url", type="string", example="/storage/files/filename.pdf"),
     *                 @OA\Property(property="filename", type="string", example="filename.pdf"),
     *                 @OA\Property(property="file_type", type="string", example="pdf"),
     *                 @OA\Property(property="file_size", type="integer", example=1024)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can upload files"),
     * )
     */
    public function uploadFile(UploadFileRequest $request): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || !in_array($user->role, [UserRole::ADMIN, UserRole::GURU], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Only Admin and Guru can upload files',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $file = $request->file('file');
            
            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . \Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Store file
            $path = Storage::disk('public')
                ->putFileAs('files', $file, $filename);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'url' => '/storage/' . $path,
                    'filename' => $filename,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
