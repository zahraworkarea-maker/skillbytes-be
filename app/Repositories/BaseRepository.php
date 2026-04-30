<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Base Repository class for all repositories
 *
 * Provides common database operations (CRUD)
 */
abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Get paginated records
     */
    public function paginate($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find record by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Find record by ID or fail
     */
    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find record by attribute
     */
    public function findBy($attribute, $value)
    {
        return $this->model->where($attribute, $value)->first();
    }

    /**
     * Get records by attribute
     */
    public function getBy($attribute, $value)
    {
        return $this->model->where($attribute, $value)->get();
    }

    /**
     * Create new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update($id, array $data)
    {
        \Log::info('[BaseRepository] update() called', [
            'model' => class_basename($this->model),
            'id' => $id,
            'data_to_update' => $data,
        ]);

        $record = $this->findOrFail($id);
        $record->update($data);
        $record->refresh();

        \Log::info('[BaseRepository] update() complete', [
            'id' => $id,
            'updated_fields' => array_keys($data),
            'updated_record' => $record->toArray(),
        ]);

        return $record;
    }

    /**
     * Delete record
     */
    public function delete($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get query builder instance
     */
    public function query()
    {
        return $this->model->query();
    }

    /**
     * Count records
     */
    public function count()
    {
        return $this->model->count();
    }
}
