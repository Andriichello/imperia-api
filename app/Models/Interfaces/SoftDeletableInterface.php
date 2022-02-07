<?php

namespace App\Models\Interfaces;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Interface SoftDeletableInterface.
 *
 * @property Carbon|null $deleted_at
 *
 * @method Builder withTrashed()
 * @method Builder withoutTrashed()
 * @method Builder onlyTrashed()
 */
interface SoftDeletableInterface
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootSoftDeletes();

    /**
     * Cascade delete the given relationship on the given mode.
     *
     * @param string $relationship
     *
     * @return void
     */
    public function cascadeSoftRestore(string $relationship);
}
