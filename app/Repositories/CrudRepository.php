<?php

namespace App\Repositories;

use App\Models\BaseModel;
use App\Models\Traits\SoftDeletableTrait;
use App\Repositories\Interfaces\CrudRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseRepository.
 *
 * @property Model|string $model
 */
abstract class CrudRepository implements CrudRepositoryInterface
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = BaseModel::class;

    public function find(mixed $id): ?Model
    {
        return $this->builder()->find($id);
    }

    public function findOrFail(mixed $id): ?Model
    {
        return $this->builder()->findOrFail($id);
    }

    public function create(array $attributes): Model
    {
        return $this->builder()->create($attributes);
    }

    public function update(Model $model, array $attributes): bool
    {
        return $model->update($attributes);
    }

    public function updateOrCreate(array $attributes): Builder|Model
    {
        return $this->builder()->updateOrCreate($attributes);
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function restore(Model $model): bool
    {
        return method_exists($model, 'restore') ? $model->restore() : false;
    }

    public function model(): string
    {
        return $this->model;
    }

    public function builder(): Builder
    {
        return ($this->model)::query();
    }

    public function isSoftDeletable(): bool
    {
        return usesTrait($this->model, SoftDeletableTrait::class);
    }
}
