<?php

namespace App\Http\Controllers;

use App\DTOs\CreateUserDto;
use App\DTOs\UpdateUserDto;
use App\Enums\UserRole;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Get list of users with pagination, search, and sort
     * GET /api/users
     * Parameters: pagesize=15, pagenumber=1, q=search, sortby=created_at, sorttype=desc
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $pageSize = (int)$request->get('pagesize', 15);
            $pageNumber = (int)$request->get('pagenumber', 1);
            $search = $request->get('q', null);
            $sortBy = $request->get('sortby', 'created_at');
            $sortType = $request->get('sorttype', 'desc');

            $users = $this->userService->getAllUsersWithPagination(
                $search,
                $sortBy,
                $sortType,
                $pageSize,
                $pageNumber
            );

            return $this->paginatedResponse($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get all users without pagination
     * GET /api/users/all
     * Authorization: Admin and Guru only
     */
    public function getAllUsers(): JsonResponse
    {
        try {
            // Authorization check: Only admin and guru can view all users
            if (!in_array(auth()->user()->role, [UserRole::ADMIN, UserRole::GURU])) {
                return $this->errorResponse('Forbidden: Only admin and guru can view all users', 403);
            }

            $users = $this->userService->getAllUsers();
            return $this->successResponse(
                UserResource::collection($users),
                'All users retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get user by ID
     * GET /api/users/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            return $this->successResponse(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->notFoundResponse('User not found');
        }
    }

    /**
     * Create new user
     * POST /api/users
     * Authorization: Admin and Guru only
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            // Authorization check: Only admin and guru can create users
            if (!in_array($request->user()->role, [UserRole::ADMIN, UserRole::GURU])) {
                return $this->errorResponse('Forbidden: Only admin and guru can create users', 403);
            }

            $dto = CreateUserDto::fromArray($request->validated());

            // Handle profile photo upload
            $profilePhoto = $request->file('profile_photo');
            $user = $this->userService->createUser($dto, $profilePhoto);
            return $this->createdResponse(
                new UserResource($user),
                'User created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update user
     * POST /api/auth/user/{user} - Use POST for form-data/file upload (RECOMMENDED)
     * PUT /api/auth/user/{user} - Use PUT for JSON body only
     * PATCH /api/auth/user/{user} - Use PATCH for JSON body only
     *
     * Note: PUT/PATCH with multipart/form-data won't parse form data due to PHP limitation.
     * Always use POST when sending form-data or files.
     *
     * Authorization: All authenticated users can update any user
     */
    public function update(UpdateUserRequest $request, $user): JsonResponse
    {
        try {
            $userId = is_object($user) ? $user->id : $user;
            $validated = $request->validated();

            \Log::info('[UserController] Update request - DETAILED', [
                'user_id' => $userId,
                'validated_data' => $validated,
                'all_input' => $request->all(),
                'all_files' => $request->allFiles(),
                'has_profile_photo' => $request->hasFile('profile_photo'),
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'validated_keys' => array_keys($validated),
            ]);

            $dto = UpdateUserDto::fromArray($validated);

            // If validated is empty but we have input, use all() to get form data
            if (empty($validated) && !empty($request->all())) {
                \Log::warning('[UserController] Validated empty but has input, using all()', [
                    'all' => $request->all(),
                ]);
                $dto = UpdateUserDto::fromArray($request->all());
            }

            // Handle profile photo upload
            $profilePhoto = $request->file('profile_photo');
            if ($profilePhoto) {
                \Log::info('[UserController] Photo file detected', [
                    'filename' => $profilePhoto->getClientOriginalName(),
                    'size' => $profilePhoto->getSize(),
                ]);
            }

            $this->userService->updateUser($userId, $dto, $profilePhoto);
            $updatedUser = $this->userService->getUserById($userId);

            return $this->successResponse(
                new UserResource($updatedUser),
                'User updated successfully'
            );
        } catch (\Exception $e) {
            \Log::error('[UserController] Update failed: ' . $e->getMessage());
            return $this->notFoundResponse('User not found');
        }
    }

    /**
     * Delete user
     * DELETE /api/users/{id}
     * Authorization: Admin and Guru only
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Authorization check: Only admin and guru can delete users
            if (!in_array(auth()->user()->role, [UserRole::ADMIN, UserRole::GURU])) {
                return $this->errorResponse('Forbidden: Only admin and guru can delete users', 403);
            }

            $this->userService->deleteUser($id);
            return $this->successResponse(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('User not found');
        }
    }

    /**
     * Update user password
     * PUT /api/auth/user/update-password/{id}
     * Authorization: User can change own password, admin can change any
     */
    public function updatePassword(Request $request, $id): JsonResponse
    {
        try {
            // Authorization check: Users can only change their own password, admin can change any
            if (auth()->user()->role !== UserRole::ADMIN && auth()->user()->id != $id) {
                return $this->errorResponse('Forbidden: You can only change your own password', 403);
            }

            $request->validate([
                'old_password' => 'required|string|min:8',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $this->userService->updatePassword(
                $id,
                $request->get('old_password'),
                $request->get('password')
            );

            return $this->successResponse(null, 'Password updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Login user
     * POST /api/users/login
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $result = $this->userService->loginUser(
                $request->validated()['email'],
                $request->validated()['password']
            );

            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }
}
