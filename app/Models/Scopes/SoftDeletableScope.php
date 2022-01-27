<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Class SoftDeletableScope.
 */
class SoftDeletableScope extends SoftDeletingScope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $trashed = request('trashed', 'without');
        if ($trashed === 'with') {
            $builder->withTrashed();
        } else if ($trashed === 'without') {
            $builder->withoutTrashed();
        } else if ($trashed === 'only') {
            $builder->onlyTrashed();
        }
    }
}
