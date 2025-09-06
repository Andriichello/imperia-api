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

    public static function getRecordRouteKeyName(): ?string
    {
        $name = parent::getRecordRouteKeyName();

        $modelClass = static::getModel();
        $model = new $modelClass();

        // Default to the model's route key name if none is set on the resource
        $name = $name ?: $model->getRouteKeyName();

        // Qualify the column with the table name to avoid ambiguity when joins are present
        if (!str_contains($name, '.')) {
            $name = $model->getTable() . '.' . $name;
        }

        return $name;
    }
}
