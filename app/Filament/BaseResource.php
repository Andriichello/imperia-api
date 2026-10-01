<?php

namespace App\Filament;

use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Queries\BaseQueryBuilder;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseResource.
 */
abstract class BaseResource extends Resource
{
    /**
     * Archived records stay visible to admins, soft-deleted ones
     * only show up through the trashed filter.
     *
     * @return BaseQueryBuilder
     */
    public static function getEloquentQuery(): BaseQueryBuilder
    {
        /** @var BaseQueryBuilder $query */
        $query = parent::getEloquentQuery();

        return $query->withoutGlobalScope(ArchivedScope::class)
            ->index(request()->user());
    }

    /**
     * Resolve the record for record pages (e.g. edit). Soft-deleted
     * records can still be opened, so they can be restored.
     *
     * @param int|string $key
     *
     * @return Model|null
     */
    public static function resolveRecordRouteBinding(int|string $key): ?Model
    {
        $query = static::getEloquentQuery()
            ->withoutGlobalScope(SoftDeletableScope::class);

        return app(static::getModel())
            ->resolveRouteBindingQuery($query, $key, static::getRecordRouteKeyName())
            ->first();
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
