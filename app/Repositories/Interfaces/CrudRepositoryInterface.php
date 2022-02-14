<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface CrudRepositoryInterface
 */
interface CrudRepositoryInterface
{
    /**
     * Find the model by the id.
     *
     * @param mixed $id
     *
     * @return Model|null
     */
    public function find(mixed $id): ?Model;

    /**
     * Find the model by the id or throw an exception.
     *
     * @param mixed $id
     *
     * @return Model|null
     */
    public function findOrFail(mixed $id): ?Model;

    /**
     * Create model with given attributes.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Update model with given attributes.
     *
     * @param Model $model
     * @param array $attributes
     *
     * @return bool
     */
    public function update(Model $model, array $attributes): bool;

    /**
     * Update or create model with given attributes.
     *
     * @param array $attributes
     *
     * @return Builder|Model
     */
    public function updateOrCreate(array $attributes): Builder|Model;

    /**
     * Delete model with given attributes.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function delete(Model $model): bool;

    /**
     * Restore model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function restore(Model $model): bool;

    /**
     * Get repository's model class.
     *
     * @return Model|string
     */
    public function model(): Model|string;

    /**
     * Get query builder for the repository's model.
     *
     * @return Builder
     */
    public function builder(): Builder;
}
