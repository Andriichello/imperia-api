<?php

namespace App\Filament;

use App\Queries\BaseQueryBuilder;
use Filament\Resources\Resource;

/**
 * Class CategoryResource.
 */
abstract class BaseResource extends Resource
{
    public static function getEloquentQuery(): BaseQueryBuilder
    {
        /** @var BaseQueryBuilder $query */
        $query = parent::getEloquentQuery();

        return $query->withoutGlobalScopes()
            ->index(request()->user());
    }

    public static function getGlobalSearchEloquentQuery(): BaseQueryBuilder
    {
        /** @var BaseQueryBuilder $query */
        $query = parent::getGlobalSearchEloquentQuery();

        return $query->withoutGlobalScopes()
            ->index(request()->user());
    }
}
