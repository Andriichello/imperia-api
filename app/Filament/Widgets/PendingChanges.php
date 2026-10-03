<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MenuVersionResource;
use App\Filament\Tables\ScheduledChangesTable;
use App\Queries\MenuVersionQueryBuilder;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PendingChanges.
 *
 * Dashboard table of the scheduled changes, which haven't gone live yet
 * (failed ones included, they come first as their time has passed).
 */
class PendingChanges extends TableWidget
{
    /**
     * @var int|null
     */
    protected static ?int $sort = 1;

    /**
     * @var int|string|array<string, int|null>
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * @var string|null
     */
    protected static ?string $heading = 'Upcoming scheduled changes';

    /**
     * Configure the table.
     *
     * @param Table $table
     *
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                /** @var MenuVersionQueryBuilder $query */
                $query = MenuVersionResource::getEloquentQuery();

                return $query->pending();
            })
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['restaurant', 'creator', 'itemChanges.target']))
            ->columns(ScheduledChangesTable::versionColumns())
            ->defaultSort('goes_live_at')
            ->paginated([5])
            ->emptyStateHeading('No upcoming changes')
            ->emptyStateDescription('Changes scheduled on a menu, category, dish or size page show up here.')
            ->headerActions([
                Action::make('all')
                    ->label('All scheduled changes')
                    ->link()
                    ->url(MenuVersionResource::getUrl()),
            ])
            ->actions(ScheduledChangesTable::versionActions());
    }
}
