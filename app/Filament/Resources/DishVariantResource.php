<?php

namespace App\Filament\Resources;

use App\Enums\WeightUnit;
use App\Filament\Actions\SchedulePriceChangeBulkAction;
use App\Filament\BaseResource;
use App\Filament\Fields\LiveFields;
use App\Filament\RelationManagers\AlterationsRelationManager;
use App\Filament\Filters\LiveFilter;
use App\Filament\Filters\TrashedFilter;
use App\Filament\Resources\DishVariantResource\Pages;
use App\Filament\Tables\AlterationsTable;
use App\Filament\Tables\Columns\LiveColumn;
use App\Models\DishVariant;
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
                ...static::getAlterableFields(),
            ]);
    }

    /**
     * Fields that can also be changed in advance, through a scheduled change (alteration).
     *
     * @return array
     */
    public static function getAlterableFields(): array
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
            ...LiveFields::make('archived'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => AlterationsTable::withScheduledChangesCount(
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
                LiveColumn::make('archived'),
                AlterationsTable::scheduledColumn(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                LiveFilter::make()->hiddenBy('archived'),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getRelations(): array
    {
        return [
            AlterationsRelationManager::class,
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
