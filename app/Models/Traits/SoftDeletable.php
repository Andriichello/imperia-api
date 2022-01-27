<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Trait SoftDeletable.
 *
 * @mixin BaseModel
 */
trait SoftDeletable
{
    use SoftDeletes;
    use CascadeSoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::restored(function ($model) {
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
            if (usesTrait($item, SoftDeletable::class)) {
                // should restore only last deleted items, so if some of them
                // was deleted (earlier) on purpose then they will stay deleted
                if ($item->deleted_at == $lastDeletedAt) {
                    $item->restore();
                }
            }
        }
    }
}
