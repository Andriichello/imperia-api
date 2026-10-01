<?php

namespace App\Filament\RelationManagers;

use App\Filament\Tables\AlterationsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AlterationsRelationManager.
 *
 * "Scheduled changes" of a dish, variant, category or menu, shown on its edit page.
 */
class AlterationsRelationManager extends RelationManager
{
    /**
     * @var string
     */
    protected static string $relationship = 'alterations';

    /**
     * @var string|null
     */
    protected static ?string $title = 'Scheduled changes';

    /**
     * @var string|null
     */
    protected static ?string $modelLabel = 'scheduled change';

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
            ->modifyQueryUsing(fn (Builder $query) => $query->with('restaurant'))
            ->columns(AlterationsTable::columns())
            ->defaultSort('perform_at', 'desc')
            ->emptyStateHeading('No scheduled changes')
            ->emptyStateDescription('Schedule a change to update values (e.g. the price) at a given time.')
            ->headerActions([
                AlterationsTable::scheduleAction(),
            ])
            ->actions(AlterationsTable::actions());
    }
}
