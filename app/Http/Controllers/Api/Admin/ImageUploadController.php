<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Media",
 *     description="Media management (Admin/Guru only)"
 * )
 */
class ImageUploadController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/upload-image",
     *     operationId="uploadImage",
     *     tags={"Media"},
     *     summary="Upload an image (Admin/Guru only)",
     *     description="Upload an image for PBL cases or section items. Only accessible to Admin and Guru roles.",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Image file",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     description="Image file to upload",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="url", type="string", example="/storage/pbl/filename.jpg"),
     *             @OA\Property(property="filename", type="string"),
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can upload images"),
     * )
     */
    public function upload(UploadImageRequest $request): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || !in_array($user->role, [UserRole::ADMIN, UserRole::GURU], true)) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin and Guru can upload images',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $image = $request->file('image');
            
            // Generate unique filename
            $filename = time() . '_' . \Str::random(8) . '.' . $image->getClientOriginalExtension();
            
            // Store file
            $path = Storage::disk('public')
                ->putFileAs('pbl', $image, $filename);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'url' => '/storage/' . $path,
                'filename' => $filename,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload image',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
