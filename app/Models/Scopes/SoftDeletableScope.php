<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Class SoftDeletableScope.
 *
 * @method Builder withTrashed()
 * @method Builder withoutTrashed()
 * @method Builder onlyTrashed()
 */
class SoftDeletableScope extends SoftDeletingScope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $type = request('filter.deleted', request('deleted', 'without'));
        if (in_array($type, ['only', 'with', 'without'])) {
            $method = $type . 'Trashed';
            $builder->$method();
        }
    }
}
