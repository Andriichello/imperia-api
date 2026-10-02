<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Fields\LiveFields;
use App\Filament\RelationManagers\AlterationsRelationManager;
use App\Filament\Filters\LiveFilter;
use App\Filament\Filters\TrashedFilter;
use App\Filament\Resources\DishCategoryResource\Pages;
use App\Filament\Tables\AlterationsTable;
use App\Filament\Tables\Columns\LiveColumn;
use App\Models\DishCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class DishCategoryResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DishCategoryResource extends BaseResource
{
    protected static ?string $model = DishCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Dish Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Category';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('menu_id')
                    ->label('Menu')
                    ->options(fn () => DishMenuResource::getSelectOptions())
                    ->in(fn () => array_keys(DishMenuResource::getSelectOptions()))
                    ->required()
                    ->searchable(),
                TextInput::make('slug')
                    ->maxLength(255),
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
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->maxLength(1020)
                ->columnSpanFull(),
            ...LiveFields::make(),
            TextInput::make('popularity')
                ->numeric()
                ->nullable()
                ->helperText('Higher numbers come first on the website. The list can also be reordered by dragging.'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => AlterationsTable::withScheduledChangesCount($query))
            ->reorderable('popularity')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('menu.title')
                    ->label('Menu')
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHas(
                        'menu',
                        fn (Builder $query) => static::searchTranslated($query, 'dish_menus.title', $search)
                    )),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return static::searchTranslated($query, 'dish_categories.title', $search);
                    }),
                LiveColumn::make(),
                AlterationsTable::scheduledColumn(),
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
     * Categories of the given menu, hidden and archived ones included.
     *
     * @param int|null $menuId
     *
     * @return array<int, string>
     */
    public static function getSelectOptions(?int $menuId): array
    {
        if (!$menuId) {
            return [];
        }

        return static::getEloquentQuery()
            ->where('dish_categories.menu_id', $menuId)
            ->get()
            ->sortBy(fn ($category) => mb_strtolower((string) $category->getAttribute('title')))
            // @phpstan-ignore-next-line
            ->mapWithKeys(function (DishCategory $category) {
                return [$category->id => $category->title . static::getStatusSuffix($category)];
            })->all();
    }

    /**
     * Categories the current user can pick, hidden and archived ones included, grouped by their menu.
     *
     * @return array<string, array<int, string>>
     */
    public static function getGroupedSelectOptions(): array
    {
        $menus = DishMenuResource::getSelectOptions();
        $groups = [];

        static::getEloquentQuery()
            ->whereIn('dish_categories.menu_id', array_keys($menus))
            ->get()
            // @phpstan-ignore-next-line
            ->sortBy(fn (DishCategory $category) => mb_strtolower($category->title))
            // @phpstan-ignore-next-line
            ->each(function (DishCategory $category) use ($menus, &$groups) {
                $groups[$menus[$category->menu_id]][$category->id] = $category->title
                    . static::getStatusSuffix($category);
            });

        return $groups;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDishCategories::route('/'),
            'create' => Pages\CreateDishCategory::route('/create'),
            'edit' => Pages\EditDishCategory::route('/{record}/edit'),
        ];
    }
}
