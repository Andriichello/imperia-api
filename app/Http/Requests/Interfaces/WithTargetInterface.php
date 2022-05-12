<?php

namespace App\Http\Requests\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface WithTargetInterface.
 */
interface WithTargetInterface
{
    /**
     * Get route id parameter.
     *
     * @return mixed
     */
    public function id(): mixed;

    /**
     * Get model with target id.
     *
     * @param Model|string $model
     *
     * @return ?Model
     */
    public function target(Model|string $model): ?Model;

    /**
     * Get trashed model with target id.
     *
     * @param Model|string $model
     *
     * @return ?Model
     */
    public function trashedTarget(Model|string $model): ?Model;
}
