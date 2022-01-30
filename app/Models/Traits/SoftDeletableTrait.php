<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Scopes\SoftDeletableScope;
use Carbon\Carbon;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

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
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        static::bootTraits();
        static::bootSoftDeletes();
        static::bootCascadeSoftDeletes();

        static::restored(function ($model) {
            $model->runCascadingRestores();
        });
    }

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
     * Run the cascading soft restore for this model.
     *
     * @return void
     */
    protected function runCascadingRestores()
    {
        foreach ($this->getActiveCascadingDeletes() as $relationship) {
            $this->cascadeSoftRestore($relationship);
        }
    }

    /**
     * Cascade delete the given relationship on the given mode.
     *
     * @param string $relationship
     * @return void
     */
    public function cascadeSoftRestore(string $relationship)
    {
        $trashed = $this->$relationship()->onlyTrashed()->get();
        $lastDeletedAt = $trashed->max('deleted_at');

        foreach ($trashed as $item) {
            if (usesTrait($item, SoftDeletableTrait::class)) {
                // should restore only last deleted items, so if some of them
                // was deleted (earlier) on purpose then they will stay deleted
                if ($item->deleted_at == $lastDeletedAt) {
                    $item->restore();
                }
            }
        }
    }
}
