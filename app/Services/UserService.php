<?php

namespace App\Services;

use App\DTOs\CreateUserDto;
use App\DTOs\UpdateUserDto;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

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
    public function createUser(CreateUserDto $dto)
    {
        try {
            $data = $dto->toArray();
            // Remove null values
            $data = array_filter($data, fn($value) => $value !== null);
            $data['password'] = Hash::make($data['password']);
            return $this->userRepository->create($data);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Update user
     */
    public function updateUser($id, UpdateUserDto $dto)
    {
        try {
            $data = $dto->toArray();
            // Remove null values
            $data = array_filter($data, fn($value) => $value !== null);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            return $this->userRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->handleError($e);
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
