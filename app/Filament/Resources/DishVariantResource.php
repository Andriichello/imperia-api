<?php

namespace App\Filament\Resources;

use App\Enums\WeightUnit;
use App\Filament\Actions\SchedulePriceChangeBulkAction;
use App\Filament\BaseResource;
use App\Filament\Fields\LiveFields;
use App\Filament\RelationManagers\ScheduledChangesRelationManager;
use App\Filament\Filters\LiveFilter;
use App\Filament\Filters\TrashedFilter;
use App\Filament\Resources\DishVariantResource\Pages;
use App\Filament\Tables\ScheduledChangesTable;
use App\Filament\Tables\Columns\LiveColumn;
use App\Models\DishVariant;
use App\Repositories\Editor\DishEditorRepository;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class DishVariantResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DishVariantResource extends BaseResource
{
    protected static ?string $model = DishVariant::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Dish Management';

    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Variant';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('dish_id')
                    ->label('Dish')
                    ->options(fn () => DishResource::getSelectOptions())
                    ->in(fn () => array_keys(DishResource::getSelectOptions()))
                    ->required()
                    ->searchable(),
                ...static::getSchedulableFields(),
            ]);
    }

    /**
     * Fields that can also be changed in advance, through a scheduled change.
     *
     * @return array
     */
    public static function getSchedulableFields(): array
    {
        return [
            ...static::getSizeFields(),
            ...LiveFields::make(),
        ];
    }

    /**
     * Values of a size.
     *
     * @return array
     */
    public static function getSizeFields(): array
    {
        return [
            TextInput::make('price')
                ->numeric()
                ->minValue(0)
                ->required(),
            TextInput::make('weight')
                ->maxLength(255),
            Select::make('weight_unit')
                ->options(array_flip(WeightUnit::getMap())),
            TextInput::make('calories')
                ->numeric()
                ->minValue(0)
                ->nullable(),
            TextInput::make('preparation_time')
                ->label('Preparation Time (minutes)')
                ->numeric()
                ->minValue(0)
                ->nullable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => ScheduledChangesTable::withScheduledChangesCount(
                $query->with('dish.menu.restaurant')
            ))
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('dish.title')
                    ->label('Dish')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money(fn (DishVariant $record): string => $record->dish->menu->restaurant->currency ?: 'UAH')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dish_variants.weight', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('weight_unit')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dish_variants.weight_unit', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('calories')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('preparation_time')
                    ->label('Prep Time (min)')
                    ->numeric()
                    ->sortable(),
                LiveColumn::make(),
                static::archivedColumn(),
                ScheduledChangesTable::scheduledColumn(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                LiveFilter::make(),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ...static::archiveActions(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    SchedulePriceChangeBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Column, which shows that a size is archived: off the menu, it can be restored.
     *
     * @return Tables\Columns\IconColumn
     */
    public static function archivedColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('archived')
            ->alignCenter()
            ->icon(fn ($state) => $state ? 'heroicon-o-archive-box' : null)
            ->color('gray')
            ->tooltip(fn ($state) => $state ? 'Archived: off the menu, it can be restored' : null);
    }

    /**
     * Actions, which archive the size and restore it from the archive.
     *
     * @return Tables\Actions\Action[]
     */
    public static function archiveActions(): array
    {
        $repository = fn () => app(DishEditorRepository::class);

        return [
            Tables\Actions\Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('The size is off the menu, until it\'s restored.')
                ->authorize('update')
                ->hidden(fn (DishVariant $record) => $record->archived || $record->trashed() || $record->isLastShown())
                ->action(fn (DishVariant $record) => $repository()->archive($record)),
            Tables\Actions\Action::make('unarchive')
                ->label('Restore from archive')
                ->icon('heroicon-o-arrow-uturn-left')
                ->authorize('update')
                ->visible(fn (DishVariant $record) => $record->archived && !$record->trashed())
                ->action(fn (DishVariant $record) => $repository()->unarchive($record)),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ScheduledChangesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDishVariants::route('/'),
            'create' => Pages\CreateDishVariant::route('/create'),
            'edit' => Pages\EditDishVariant::route('/{record}/edit'),
        ];
    }
}
