<?php

namespace App\Models\Scopes;

use App\Models\BanquetState;
use App\Models\Customer;
use App\Models\ImperiaUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SoftDeletableScope extends SoftDeletingScope
{
    public function apply(Builder $builder, Model $model)
    {
        if (str_contains(request()->getUri(), '/banquets')) {
            if ($model instanceof Customer || $model instanceof ImperiaUser || $model instanceof BanquetState) {
                $builder->withTrashed();
                return;
            }
        }

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
