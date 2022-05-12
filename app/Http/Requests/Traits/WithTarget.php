<?php

namespace App\Http\Requests\Traits;

use App\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait WithTarget.
 *
 * @mixin BaseRequest
 */
trait WithTarget
{
    /**
     * Last loaded target model.
     *
     * @var Model|null
     */
    protected ?Model $target;

    /**
     * Get route id parameter.
     *
     * @return mixed
     */
    public function id(): mixed
    {
        return $this->route('id');
    }

    /**
     * Get model with target id.
     *
     * @param Model|string $model
     *
     * @return ?Model
     */
    public function target(Model|string $model): ?Model
    {
        if (isset($this->target) && is_a($this->target, $model)) {
            return $this->target;
        }

        return $this->target = $model::query()->find($this->id());
    }

    /**
     * Get trashed model with target id.
     *
     * @param Model|string $model
     *
     * @return ?Model
     */
    public function trashedTarget(Model|string $model): ?Model
    {
        if (isset($this->target) && is_a($this->target, $model)) {
            return $this->target;
        }

        /** @phpstan-ignore-next-line */
        return $this->target = $model::query()->onlyTrashed()->find($this->id());
    }
}
