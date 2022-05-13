<?php

namespace App\Http\Requests\Traits;

use App\Http\Requests\BaseRequest;
use App\Models\Traits\SoftDeletableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Trait WithTarget.
 *
 * @mixin BaseRequest
 */
trait WithTarget
{
    /**
     * Target id.
     *
     * @var Model|null
     */
    protected mixed $id;

    /**
     * Last loaded target model.
     *
     * @var Model|null
     */
    protected ?Model $target;

    /**
     * Get|set target id.
     *
     * @param mixed $id
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function id(mixed $id = false): mixed
    {
        if ($id !== false) {
            return $this->id = $id;
        }

        if (isset($this->id)) {
            return $this->id;
        }

        return $this->id = $this->route('id');
    }

    /**
     * Get query for retrieving target model.
     *
     * @param Model|string $model
     *
     * @return Builder
     */
    protected function targetQuery(Model|string $model): Builder
    {
        $builder = $model::query()->whereKey($this->id());
        if (usesTrait($model, SoftDeletableTrait::class)) {
            // @phpstan-ignore-next-line
            $builder->withTrashed();
        }

        return $builder;
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

        return $this->target = $this->targetQuery($model)->first();
    }

    /**
     * Get model with target id or throw an exception.
     *
     * @param Model|string $model
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function targetOrFail(Model|string $model): Model
    {
        if (isset($this->target) && is_a($this->target, $model)) {
            return $this->target;
        }

        return $this->target = $this->targetQuery($model)->firstOrFail();
    }
}
