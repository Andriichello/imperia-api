<?php

namespace App\Filament\Resources;

use App\Enums\ProductFlag;
use App\Enums\WeightUnit;
use App\Filament\BaseResource;
use App\Filament\Fields\FlagFields;
use App\Filament\RelationManagers\AlterationsRelationManager;
use App\Filament\Filters\LiveFilter;
use App\Filament\Filters\TrashedFilter;
use App\Filament\Resources\DishResource\Pages;
use App\Filament\Resources\DishResource\RelationManagers\VariantsRelationManager;
use App\Filament\Tables\AlterationsTable;
use App\Filament\Tables\Columns\LiveColumn;
use App\Models\Dish;
use App\Models\DishVariant;
use App\Models\Scopes\ArchivedScope;
use App\Queries\DishQueryBuilder;
use App\Filament\Forms\Components\MediaAttachmentField;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
                ...static::getPlacementFields(),
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
     * Menu and category of the dish.
     *
     * @return array
     */
    public static function getPlacementFields(): array
    {
        return [
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
        ];
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
            ...FlagFields::make(),
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
                LiveColumn::make(),
                AlterationsTable::scheduledColumn(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('restaurant')
                    ->options(fn () => RestaurantResource::getEloquentQuery()->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $id) => $query->whereHas('menu', fn (Builder $query) => $query
                            ->where('dish_menus.restaurant_id', $id))
                    ))
                    ->visible(fn () => !request()->user()?->restaurant_id),
                Tables\Filters\SelectFilter::make('menu')
                    ->attribute('dishes.menu_id')
                    ->options(fn () => DishMenuResource::getSelectOptions())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('flags')
                    ->label('Tags & allergens')
                    ->multiple()
                    ->options([
                        'Tags' => ProductFlag::getTagLabels(),
                        'Hotness' => ProductFlag::getHotnessLabels(),
                        'Allergens' => ProductFlag::getAllergenLabels(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        /** @var DishQueryBuilder $query */
                        $query->withAnyOfFlags(...($data['values'] ?? []));
                    }),
                LiveFilter::make(),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                static::duplicateAction(Tables\Actions\Action::class),
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
            VariantsRelationManager::class,
            AlterationsRelationManager::class,
        ];
    }

    /**
     * Action, which duplicates the dish with its variants and images, as an archived draft.
     *
     * @param class-string<Tables\Actions\Action|Actions\Action> $class
     *
     * @return Tables\Actions\Action|Actions\Action
     */
    public static function duplicateAction(string $class): Tables\Actions\Action|Actions\Action
    {
        return $class::make('duplicate')
            ->label('Duplicate')
            ->icon('heroicon-m-square-2-stack')
            ->authorize('create')
            ->hidden(fn (Dish $record) => $record->trashed())
            ->modalHeading(fn (Dish $record) => "Duplicate $record->title")
            ->modalDescription('The copy is archived, until you make it live. '
                . 'Variants, images and tags are copied too, scheduled changes are not.')
            ->modalSubmitActionLabel('Duplicate')
            ->form([
                ...static::getPlacementFields(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ])
            ->fillForm(fn (Dish $record) => [
                'menu_id' => $record->menu_id,
                'category_id' => $record->category_id,
                'title' => "$record->title (copy)",
            ])
            ->action(function (Dish $record, array $data, Tables\Actions\Action|Actions\Action $action) {
                $copy = static::duplicate($record, $data);

                Notification::make()
                    ->title('The dish was duplicated')
                    ->success()
                    ->send();

                $action->redirect(static::getUrl('edit', ['record' => $copy]));
            });
    }

    /**
     * Copy the dish with its variants (archived ones included) and images.
     * The copy is archived and gets no slug, scheduled changes aren't copied.
     *
     * @param Dish $dish
     * @param array $data Values of the copy: `menu_id`, `category_id` and `title`.
     *
     * @return Dish
     */
    public static function duplicate(Dish $dish, array $data): Dish
    {
        return DB::transaction(function () use ($dish, $data) {
            /** @var Dish $copy */
            $copy = $dish->replicate(['slug', 'scheduled_changes_count']);
            $copy->fill(Arr::only($data, ['menu_id', 'category_id', 'title']));
            $copy->archived = true;
            // the copy isn't a copy of the old menu (see `dishes:copy-old-menu`)
            $copy->setJson('metadata', Arr::except($copy->getJson('metadata'), 'copied_from'));
            $copy->save();

            /** @var DishVariant $variant */
            foreach ($dish->variants()->withoutGlobalScope(ArchivedScope::class)->get() as $variant) {
                $variant->replicate()
                    ->fill(['dish_id' => $copy->id])
                    ->save();
            }

            $copy->media()->attach($dish->media()
                ->pluck('mediables.order', 'media.id')
                ->map(fn ($order) => ['order' => $order])
                ->all());

            return $copy;
        });
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
