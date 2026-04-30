<?php

namespace App\Services;

use App\DTOs\CreateUserDto;
use App\DTOs\UpdateUserDto;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UserService extends BaseService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    /**
     * Get all users with search, sort, and pagination
     */
    public function getAllUsersWithPagination(
        ?string $search = null,
        ?string $sortBy = 'created_at',
        ?string $sortType = 'desc',
        int $pageSize = 15,
        int $pageNumber = 1
    ) {
        return $this->userRepository->searchWithPagination(
            $search,
            $sortBy,
            $sortType,
            $pageSize,
            $pageNumber
        );
    }

    /**
     * Get all users (no pagination)
     */
    public function getAllUsers()
    {
        return $this->userRepository->getAllUsers();
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        return $this->userRepository->findOrFail($id);
    }

    /**
     * Create new user
     */
    public function createUser(CreateUserDto $dto, ?UploadedFile $profilePhoto = null)
    {
        try {
            $data = $dto->toArray();
            // Remove null values
            $data = array_filter($data, fn($value) => $value !== null);
            $data['password'] = Hash::make($data['password']);

            // Create user first
            $user = $this->userRepository->create($data);

            // Handle profile photo upload
            if ($profilePhoto) {
                \Log::info('[UserService] Profile photo received during user creation', [
                    'filename' => $profilePhoto->getClientOriginalName(),
                    'size' => $profilePhoto->getSize(),
                    'mime' => $profilePhoto->getMimeType(),
                ]);
                $photoPath = $this->storeProfilePhoto($profilePhoto, $user->id);
                $user = $this->userRepository->update($user->id, ['profile_photo_path' => $photoPath]);
                \Log::info('[UserService] Photo saved with path: ' . $photoPath);
            }

            return $user;
        } catch (\Exception $e) {
            \Log::error('[UserService] Create user error: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Update user
     */
    public function updateUser($id, UpdateUserDto $dto, ?UploadedFile $profilePhoto = null)
    {
        try {
            $data = $dto->toArray();
            \Log::info('[UserService] updateUser START', [
                'user_id' => $id,
                'dto_array' => $data,
            ]);

            // Remove null values
            $data = array_filter($data, fn($value) => $value !== null);
            \Log::info('[UserService] After filter', [
                'filtered_data' => $data,
            ]);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Handle profile photo upload
            if ($profilePhoto) {
                \Log::info('[UserService] Profile photo received', [
                    'filename' => $profilePhoto->getClientOriginalName(),
                    'size' => $profilePhoto->getSize(),
                    'mime' => $profilePhoto->getMimeType(),
                ]);
                $photoPath = $this->storeProfilePhoto($profilePhoto, $id);
                $data['profile_photo_path'] = $photoPath;
                \Log::info('[UserService] Photo saved with path: ' . $photoPath);
            } else {
                \Log::info('[UserService] No profile photo provided');
            }

            return $this->userRepository->update($id, $data);
        } catch (\Exception $e) {
            \Log::error('[UserService] Update user error: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Store profile photo and return the path
     */
    public function storeProfilePhoto(UploadedFile $file, $userId): string
    {
        try {
            \Log::info('[UserService] storeProfilePhoto START', [
                'userId' => $userId,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);

            // Delete old photo if exists
            $user = $this->userRepository->find($userId);
            if ($user && $user->profile_photo_path) {
                \Log::info('[UserService] Deleting old photo', [
                    'old_path' => $user->profile_photo_path,
                ]);
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new photo
            $path = $file->store('profile-photos', 'public');
            \Log::info('[UserService] Photo stored successfully', [
                'stored_path' => $path,
                'storage_disk' => 'public',
            ]);

            // Verify file exists
            if (Storage::disk('public')->exists($path)) {
                \Log::info('[UserService] File verified in storage');
            } else {
                \Log::warning('[UserService] File not found after storage!');
            }

            return $path;
        } catch (\Exception $e) {
            \Log::error('[UserService] Photo upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Failed to upload profile photo: ' . $e->getMessage());
        }
    }

    /**
     * Delete profile photo
     */
    public function deleteProfilePhoto($userId): bool
    {
        try {
            $user = $this->userRepository->find($userId);
            if ($user && $user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
                $this->userRepository->update($userId, ['profile_photo_path' => null]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete profile photo: ' . $e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        try {
            return $this->userRepository->delete($id);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Find user by email
     */
    public function getUserByEmail($email)
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Update user password
     */
    public function updatePassword($id, $oldPassword, $newPassword)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        if (!Hash::check($oldPassword, $user->password)) {
            throw new \Exception('Old password is incorrect');
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return $user;
    }

    /**
     * Login user (by email or username)
     */
    public function loginUser(string $emailOrUsername, string $password)
    {
        $user = $this->userRepository->findByEmailOrUsername($emailOrUsername);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid email/username or password');
        }

        if (!$user->is_active) {
            throw new \Exception('User account is inactive');
        }

        // Create token for the user
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
