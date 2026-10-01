<?php

namespace App\Filament\Filters;

use App\Models\Scopes\SoftDeletableScope;
use Filament\Tables\Filters\TrashedFilter as BaseTrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Class TrashedFilter.
 *
 * Filament's trashed filter only removes `SoftDeletingScope` from the base query,
 * but the models register their own `SoftDeletableScope` instead, which would keep
 * hiding soft-deleted records. The filter's queries take care of hiding them by default.
 */
class TrashedFilter extends BaseTrashedFilter
{
    /**
     * Set up the filter.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->baseQuery(fn (Builder $query) => $query->withoutGlobalScopes([
            SoftDeletingScope::class,
            SoftDeletableScope::class,
        ]));
    }
}
