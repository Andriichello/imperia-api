<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Resources\AlterationResource\Pages;
use App\Filament\Tables\AlterationsTable;
use App\Models\Morphs\Alteration;
use App\Queries\AlterationQueryBuilder;
use App\Queries\BaseQueryBuilder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AlterationResource.
 *
 * All scheduled changes of menus, categories, dishes and variants.
 * New ones are scheduled from the edit page of the record that should change.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AlterationResource extends BaseResource
{
    protected static ?string $model = Alteration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Dish Management';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Scheduled change';

    protected static ?string $pluralModelLabel = 'Scheduled changes';

    /**
     * Only changes of the dish models, not the old menu ones.
     *
     * @return BaseQueryBuilder
     */
    public static function getEloquentQuery(): BaseQueryBuilder
    {
        /** @var AlterationQueryBuilder $query */
        $query = parent::getEloquentQuery();

        return $query->forClasses(...array_keys(AlterationsTable::MODELS));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        $types = [];

        foreach (AlterationsTable::MODELS as $class => $model) {
            $types[(new $class())->getMorphClass()] = $model['label'];
        }

        return $table
            ->modifyQueryUsing(fn (Builder $query) => AlterationsTable::withSubjects($query))
            ->columns([
                ...AlterationsTable::subjectColumns(),
                ...AlterationsTable::columns(),
            ])
            ->defaultSort('perform_at')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Alteration::STATUS_SCHEDULED => 'Scheduled',
                        Alteration::STATUS_DUE => 'Due',
                        Alteration::STATUS_DONE => 'Done',
                        Alteration::STATUS_FAILED => 'Failed',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'] ?? null)) {
                            /** @var AlterationQueryBuilder $query */
                            $query->withStatus($data['value']);
                        }
                    }),
                Tables\Filters\SelectFilter::make('alterable_type')
                    ->label('Type')
                    ->options($types),
                Tables\Filters\SelectFilter::make('restaurant')
                    ->relationship('restaurant', 'name')
                    ->visible(fn () => !request()->user()?->restaurant_id),
                Tables\Filters\Filter::make('perform_at')
                    ->label('When')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query
                                ->whereDate('perform_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query
                                ->whereDate('perform_at', '<=', $date));
                    }),
            ])
            ->actions(AlterationsTable::actions());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlterations::route('/'),
        ];
    }
}
