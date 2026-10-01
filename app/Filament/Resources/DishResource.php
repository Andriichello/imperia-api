<?php

namespace App\Filament\Resources;

use App\Enums\ProductFlag;
use App\Enums\WeightUnit;
use App\Filament\BaseResource;
use App\Filament\RelationManagers\AlterationsRelationManager;
use App\Filament\Filters\TrashedFilter;
use App\Filament\Resources\DishResource\Pages;
use App\Filament\Tables\AlterationsTable;
use App\Models\Dish;
use App\Filament\Forms\Components\MediaAttachmentField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class DishResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DishResource extends BaseResource
{
    protected static ?string $model = Dish::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';

    protected static ?string $navigationGroup = 'Dish Management';

    protected static ?string $modelLabel = 'Dish';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('menu_id')
                    ->label('Menu')
                    ->options(fn () => DishMenuResource::getSelectOptions())
                    ->in(fn () => array_keys(DishMenuResource::getSelectOptions()))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),
                Select::make('category_id')
                    ->label('Category')
                    ->options(fn (callable $get) => DishCategoryResource::getSelectOptions((int) $get('menu_id')))
                    ->in(fn (callable $get) => array_keys(
                        DishCategoryResource::getSelectOptions((int) $get('menu_id'))
                    ))
                    ->searchable(),
                TextInput::make('slug')
                    ->maxLength(255),
                ...static::getAlterableFields(),
                MediaAttachmentField::make('media')
                    ->label('Dish Images')
                    ->modelType('dishes')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxFiles(5)
                    ->maxSize(2048)
                    ->preview(true)
                    ->multiple(true)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Fields that can also be changed in advance, through a scheduled change (alteration).
     *
     * @return array
     */
    public static function getAlterableFields(): array
    {
        $flags = [];

        foreach (ProductFlag::getMap() as $flag) {
            $flags[$flag] = $flag;
        }

        return [
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->maxLength(1020)
                ->columnSpanFull(),
            TextInput::make('price')
                ->numeric()
                ->minValue(0)
                ->required(),
            TextInput::make('weight')
                ->maxLength(255),
            Select::make('weight_unit')
                ->options(array_flip(WeightUnit::getMap())),
            TextInput::make('badge')
                ->maxLength(25),
            TextInput::make('calories')
                ->numeric()
                ->minValue(0)
                ->nullable(),
            TextInput::make('preparation_time')
                ->label('Preparation Time (minutes)')
                ->numeric()
                ->minValue(0)
                ->nullable(),
            Toggle::make('archived')
                ->default(false),
            TextInput::make('popularity')
                ->numeric()
                ->nullable(),
            Select::make('flags')
                ->multiple()
                ->searchable()
                ->options($flags),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => AlterationsTable::withScheduledChangesCount(
                $query->with('menu.restaurant')
            ))
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('menu.title')
                    ->label('Menu')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('menu', function (Builder $q) use ($search): void {
                            $q->where('dish_menus.title', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Category')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('category', function (Builder $q) use ($search): void {
                            $q->where('dish_categories.title', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dishes.title', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->money(fn (Dish $record): string => $record->menu->restaurant->currency ?: 'UAH')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dishes.weight', 'like', "%{$search}%");
                    }),
                Tables\Columns\IconColumn::make('archived')
                    ->label('Live')
                    ->alignCenter()
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),
                AlterationsTable::scheduledColumn(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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

    /**
     * Dishes the current user can pick in other forms, archived ones included.
     * Labelled with their menu (and restaurant, when several are listed).
     *
     * @return array<int, string>
     */
    public static function getSelectOptions(): array
    {
        $dishes = static::getEloquentQuery()
            ->with('menu.restaurant')
            ->orderBy('dishes.title')
            ->get();

        $withRestaurant = $dishes->pluck('menu.restaurant_id')->unique()->count() > 1;

        // @phpstan-ignore-next-line
        return $dishes->mapWithKeys(function (Dish $dish) use ($withRestaurant) {
            $parts = array_filter([
                $withRestaurant ? $dish->menu->restaurant->name : null,
                $dish->menu->title,
                $dish->title,
            ]);

            return [$dish->id => implode(' · ', $parts) . ($dish->archived ? ' (archived)' : '')];
        })->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDishes::route('/'),
            'create' => Pages\CreateDish::route('/create'),
            'edit' => Pages\EditDish::route('/{record}/edit'),
        ];
    }
}
