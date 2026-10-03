<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Resources\MenuVersionResource\Pages;
use App\Filament\Tables\ScheduledChangesTable;
use App\Models\MenuVersion;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class MenuVersionResource.
 *
 * Scheduled changes of all menus, categories, dishes and sizes: versions, which go live
 * at their time (a change scheduled on its own is a version of one change).
 */
class MenuVersionResource extends BaseResource
{
    /**
     * The model of the resource.
     *
     * @var string|null
     */
    protected static ?string $model = MenuVersion::class;

    /**
     * The navigation icon.
     *
     * @var string|null
     */
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    /**
     * The navigation group.
     *
     * @var string|null
     */
    protected static ?string $navigationGroup = 'Dish Management';

    /**
     * The order in the navigation group.
     *
     * @var int|null
     */
    protected static ?int $navigationSort = 5;

    /**
     * The label of a record.
     *
     * @var string|null
     */
    protected static ?string $modelLabel = 'Scheduled change';

    /**
     * The label of records.
     *
     * @var string|null
     */
    protected static ?string $pluralModelLabel = 'Scheduled changes';

    /**
     * Versions are made where their records are changed.
     *
     * @return bool
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * The table of the versions.
     *
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['restaurant', 'creator', 'itemChanges.target']))
            ->columns(ScheduledChangesTable::versionColumns())
            ->defaultSort('goes_live_at')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        MenuVersion::STATUS_DRAFT => 'Draft',
                        MenuVersion::STATUS_SCHEDULED => 'Scheduled',
                        MenuVersion::STATUS_INACTIVE => 'Inactive',
                        MenuVersion::STATUS_APPLIED => 'Applied',
                        MenuVersion::STATUS_FAILED => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('restaurant')
                    ->relationship('restaurant', 'name')
                    ->visible(fn () => !request()->user()?->restaurant_id),
                Tables\Filters\Filter::make('goes_live_at')
                    ->label('When')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query
                                ->whereDate('goes_live_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query
                                ->whereDate('goes_live_at', '<=', $date));
                    }),
            ])
            ->actions([
                ...ScheduledChangesTable::versionActions(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /**
     * The pages of the resource.
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuVersions::route('/'),
        ];
    }
}
