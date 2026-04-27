<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active users
     */
    public function getActiveUsers()
    {
        return $this->query()
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->findBy('email', $email);
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username)
    {
        return $this->findBy('username', $username);
    }

    /**
     * Find user by email or username
     */
    public function findByEmailOrUsername(string $emailOrUsername)
    {
        return $this->query()
            ->where('email', $emailOrUsername)
            ->orWhere('username', $emailOrUsername)
            ->first();
    }

    /**
     * Get users with pagination
     */
    public function getWithPagination($perPage = 15)
    {
        return $this->query()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get users with search, sort, and pagination
     */
    public function searchWithPagination(
        ?string $search = null,
        ?string $sortBy = 'created_at',
        ?string $sortType = 'desc',
        int $pageSize = 15,
        int $pageNumber = 1
    ) {
        $query = $this->query();

        // Search filter
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
        }

        // Sortusername', '
        if ($sortBy && in_array($sortBy, ['name', 'email', 'role', 'is_active', 'created_at', 'updated_at'])) {
            $sortType = strtolower($sortType) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortType);
        } else {
            $query->latest();
        }

        // Pagination
        return $query->paginate($pageSize, ['*'], 'page', $pageNumber);
    }

    /**
     * Get all users (no pagination)
     */
    public function getAllUsers()
    {
        return $this->query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
