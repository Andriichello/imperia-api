<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use function Sodium\add;

class SoftDeletableScope extends SoftDeletingScope
{
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
