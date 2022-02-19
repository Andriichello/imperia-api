<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Scopes\SoftDeletableScope;
use Carbon\Carbon;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

/**
 * Trait SoftDeletableTrait.
 *
 * @mixin BaseModel
 *
 * @property Carbon|null $deleted_at
 *
 * @method Builder withTrashed()
 * @method Builder withoutTrashed()
 * @method Builder onlyTrashed()
 */
trait SoftDeletableTrait
{
    use SoftDeletes;
    use CascadeSoftDeletes;

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new SoftDeletableScope());
    }

    /**
     * Boot the cascade soft deletes trait for a model.
     *
     * @throws LogicException
     */
    protected static function bootCascadeSoftDeletes()
    {
        static::deleting(function ($model) {
            $model->validateCascadingSoftDelete();

            $model->runCascadingDeletes();
        });
    }

    /**
     * Boot the soft deletable trait for a model.
     *
     * @return void
     */
    protected static function bootSoftDeletableTrait()
    {
        static::restored(function ($model) {
            $model->validateCascadingSoftDelete();

            $model->runCascadingRestores();
        });
    }

    /**
     * Run the cascading soft restore for this model.
     *
     * @return void
     */
    protected function runCascadingRestores()
    {
        foreach ($this->getActiveCascadingRestores() as $relationship) {
            $this->cascadeSoftRestore($relationship);
        }
    }

    /**
     * For the cascading restores defined on the model, return only those that are not null.
     *
     * @return array
     */
    protected function getActiveCascadingRestores(): array
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return $this->{$relationship}()->onlyTrashed()->exists();
        });
    }

    /**
     * Cascade delete the given relationship on the given mode.
     *
     * @param string $relationship
     * @return void
     */
    public function cascadeSoftRestore(string $relationship)
    {
        /** @var Builder $builder */
        $builder = $this->$relationship()->onlyTrashed();
        $maxDate = $builder->max($column = $this->getDeletedAtColumn());

        foreach ($builder->where($column, $maxDate)->get() as $item) {
            if (usesTrait($item, SoftDeletableTrait::class)) {
                // should restore only last deleted items, so if some of them
                // was deleted earlier (on purpose) then they will stay that way
                // @phpstan-ignore-next-line
                $item->restore();
            }
        }
    }
}
