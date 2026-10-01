<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LiveFilter.
 *
 * Shows all, only live or only archived records.
 */
class LiveFilter extends TernaryFilter
{
    public static function getDefaultName(): ?string
    {
        return 'live';
    }

    /**
     * Set up the filter.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Status')
            ->placeholder('All')
            ->trueLabel('Live')
            ->falseLabel('Archived')
            ->queries(
                true: fn (Builder $query) => $query->where($query->qualifyColumn('archived'), false),
                false: fn (Builder $query) => $query->where($query->qualifyColumn('archived'), true),
                blank: fn (Builder $query) => $query,
            );
    }
}
