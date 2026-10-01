<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
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
use Filament\Forms\Components\Toggle;
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
            Toggle::make('archived')
                ->default(false),
            TextInput::make('popularity')
                ->numeric()
                ->nullable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => AlterationsTable::withScheduledChangesCount($query))
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('menu.title')
                    ->label('Menu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dish_categories.title', 'like', "%{$search}%");
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
     * Categories of the given menu, archived ones included.
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
            ->orderBy('dish_categories.title')
            ->get()
            // @phpstan-ignore-next-line
            ->mapWithKeys(function (DishCategory $category) {
                return [$category->id => $category->title . ($category->archived ? ' (archived)' : '')];
            })->all();
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
