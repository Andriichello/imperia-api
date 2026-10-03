<?php

namespace App\Filament\RelationManagers;

use App\Filament\Tables\ScheduledChangesTable;
use App\Models\MenuVersionChange;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ScheduledChangesRelationManager.
 *
 * Changes of a menu, category, dish or size in scheduled versions, the latest first.
 * A change scheduled here is a version of one change.
 */
class ScheduledChangesRelationManager extends RelationManager
{
    /**
     * The relationship of the owner record.
     *
     * @var string
     */
    protected static string $relationship = 'scheduledChanges';

    /**
     * The title of the relation manager.
     *
     * @var string|null
     */
    protected static ?string $title = 'Scheduled changes';

    /**
     * The label of the related model.
     *
     * @var string|null
     */
    protected static ?string $modelLabel = 'scheduled change';

    /**
     * The policies are checked through the Gate, even without methods (see `BaseResource`).
     *
     * @return bool
     */
    public static function shouldCheckPolicyExistence(): bool
    {
        return false;
    }

    /**
     * The table of the changes.
     *
     * @param Table $table
     *
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['version.restaurant', 'version.itemChanges', 'target'])
                ->join('menu_versions', 'menu_versions.id', '=', 'menu_version_changes.version_id')
                ->orderByDesc('menu_versions.goes_live_at')
                ->select('menu_version_changes.*'))
            ->columns(ScheduledChangesTable::changeColumns())
            ->emptyStateHeading('No scheduled changes')
            ->emptyStateDescription('Schedule a change to update values (e.g. the price) at a given time.')
            ->headerActions([
                ScheduledChangesTable::scheduleAction(),
            ])
            ->actions([
                ...ScheduledChangesTable::versionActions(fn (MenuVersionChange $record) => $record->version),
                ScheduledChangesTable::removeAction(),
            ]);
    }
}
