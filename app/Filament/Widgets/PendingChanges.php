<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AlterationResource;
use App\Filament\Tables\AlterationsTable;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PendingChanges.
 *
 * Dashboard table of the scheduled changes, which aren't performed yet
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
            ->query(fn () => AlterationResource::getEloquentQuery()
                ->whereNull('alterations.performed_at'))
            ->modifyQueryUsing(fn (Builder $query) => AlterationsTable::withSubjects($query))
            ->columns([
                ...AlterationsTable::subjectColumns(),
                ...AlterationsTable::columns(),
            ])
            ->defaultSort('perform_at')
            ->paginated([5])
            ->emptyStateHeading('No upcoming changes')
            ->emptyStateDescription('Changes scheduled on a menu, category, dish or variant page show up here.')
            ->headerActions([
                Action::make('all')
                    ->label('All scheduled changes')
                    ->link()
                    ->url(AlterationResource::getUrl()),
            ])
            ->actions(AlterationsTable::actions());
    }
}
