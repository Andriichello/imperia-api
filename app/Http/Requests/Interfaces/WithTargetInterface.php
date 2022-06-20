<?php

namespace App\Http\Requests\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Interface WithTargetInterface.
 */
interface WithTargetInterface
{
    /**
     * Get|set route id parameter.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function id(mixed $id): mixed;

    /**
     * Get model with target id.
     *
     * @param Model|string $model
     *
     * @return ?Model
     */
    public function target(Model|string $model): ?Model;

    /**
     * Get model with target id or throw an exception.
     *
     * @param Model|string $model
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function targetOrFail(Model|string $model): Model;
}
