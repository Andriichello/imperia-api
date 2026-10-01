<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\RelationManagers\AlterationsRelationManager;
use App\Filament\Filters\LiveFilter;
use App\Filament\Filters\TrashedFilter;
use App\Filament\Fields\RestaurantSelect;
use App\Filament\Resources\DishMenuResource\Pages;
use App\Filament\Tables\AlterationsTable;
use App\Filament\Tables\Columns\LiveColumn;
use App\Models\DishMenu;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class DishMenuResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DishMenuResource extends BaseResource
{
    protected static ?string $model = DishMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Dish Management';

    protected static ?string $modelLabel = 'Menu';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RestaurantSelect::make()
                    ->required(),
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
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('dish_menus.title', 'like', "%{$search}%");
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
     * Menus the current user can pick in other forms, archived ones included.
     * The restaurant is prefixed when menus of several restaurants are listed.
     *
     * @return array<int, string>
     */
    public static function getSelectOptions(): array
    {
        $menus = static::getEloquentQuery()
            ->with('restaurant')
            ->orderBy('dish_menus.title')
            ->get();

        $withRestaurant = $menus->pluck('restaurant_id')->unique()->count() > 1;

        // @phpstan-ignore-next-line
        return $menus->mapWithKeys(function (DishMenu $menu) use ($withRestaurant) {
            $label = $withRestaurant
                ? $menu->restaurant->name . ' · ' . $menu->title
                : $menu->title;

            return [$menu->id => $label . ($menu->archived ? ' (archived)' : '')];
        })->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDishMenus::route('/'),
            'create' => Pages\CreateDishMenu::route('/create'),
            'edit' => Pages\EditDishMenu::route('/{record}/edit'),
        ];
    }
}
