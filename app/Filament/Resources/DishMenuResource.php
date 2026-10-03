<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Fields\LiveFields;
use App\Filament\RelationManagers\ScheduledChangesRelationManager;
use App\Filament\Filters\LiveFilter;
use App\Filament\Filters\TrashedFilter;
use App\Filament\Fields\RestaurantSelect;
use App\Filament\Resources\DishMenuResource\Pages;
use App\Filament\Tables\ScheduledChangesTable;
use App\Filament\Tables\Columns\LiveColumn;
use App\Models\DishMenu;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Menu';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RestaurantSelect::make()
                    ->required(),
                TextInput::make('slug')
                    ->maxLength(255),
                ...static::getSchedulableFields(),
                TextInput::make('popularity')
                    ->numeric()
                    ->nullable()
                    ->helperText('Higher numbers come first on the website. '
                        . 'The list can also be reordered by dragging.'),
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
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->maxLength(1020)
                ->columnSpanFull(),
            ...LiveFields::make(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => ScheduledChangesTable::withScheduledChangesCount($query))
            ->reorderable('popularity')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return static::searchTranslated($query, 'dish_menus.title', $search);
                    }),
                LiveColumn::make(),
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
            ScheduledChangesRelationManager::class,
        ];
    }

    /**
     * Menus the current user can pick in other forms, hidden and archived ones included.
     * The restaurant is prefixed when menus of several restaurants are listed.
     *
     * @return array<int, string>
     */
    public static function getSelectOptions(): array
    {
        $menus = static::getEloquentQuery()
            ->with('restaurant')
            ->get()
            ->sortBy(fn ($menu) => mb_strtolower((string) $menu->getAttribute('title')));

        $withRestaurant = $menus->pluck('restaurant_id')->unique()->count() > 1;

        // @phpstan-ignore-next-line
        return $menus->mapWithKeys(function (DishMenu $menu) use ($withRestaurant) {
            $label = $withRestaurant
                ? $menu->restaurant->name . ' · ' . $menu->title
                : $menu->title;

            return [$menu->id => $label . static::getStatusSuffix($menu)];
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
