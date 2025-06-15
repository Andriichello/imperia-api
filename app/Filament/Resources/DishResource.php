<?php

namespace App\Filament\Resources;

use App\Enums\WeightUnit;
use App\Filament\BaseResource;
use App\Filament\Resources\DishResource\Pages;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->options(DishMenu::all()->pluck('title', 'id'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),
                Select::make('category_id')
                    ->label('Category')
                    ->options(function (callable $get) {
                        $menuId = $get('menu_id');

                        if (!$menuId) {
                            return [];
                        }

                        return DishCategory::query()
                            ->where('menu_id', $menuId)
                            ->pluck('title', 'id');
                    })
                    ->searchable(),
                TextInput::make('slug')
                    ->maxLength(255),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(1020)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->numeric()
                    ->required(),
                TextInput::make('weight')
                    ->maxLength(255),
                Select::make('weight_unit')
                    ->options(array_flip(WeightUnit::getMap())),
                TextInput::make('badge')
                    ->maxLength(255),
                TextInput::make('calories')
                    ->numeric()
                    ->nullable(),
                TextInput::make('preparation_time')
                    ->label('Preparation Time (minutes)')
                    ->numeric()
                    ->nullable(),
                Toggle::make('archived')
                    ->default(false),
                TextInput::make('popularity')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('menu.title')
                    ->label('Menu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->searchable(),
                Tables\Columns\IconColumn::make('archived')
                    ->label('Live')
                    ->alignCenter()
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
