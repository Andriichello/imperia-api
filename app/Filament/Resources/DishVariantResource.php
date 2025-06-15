<?php

namespace App\Filament\Resources;

use App\Enums\WeightUnit;
use App\Filament\BaseResource;
use App\Filament\Resources\DishVariantResource\Pages;
use App\Models\Dish;
use App\Models\DishVariant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

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
    protected static ?string $modelLabel = 'Variant';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('dish_id')
                    ->label('Dish')
                    ->options(Dish::all()->pluck('title', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('price')
                    ->numeric()
                    ->required(),
                TextInput::make('weight')
                    ->maxLength(255),
                Select::make('weight_unit')
                    ->options(array_flip(WeightUnit::getMap())),
                TextInput::make('calories')
                    ->numeric()
                    ->nullable(),
                TextInput::make('preparation_time')
                    ->label('Preparation Time (minutes)')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('dish.title')
                    ->label('Dish')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->searchable(),
                Tables\Columns\TextColumn::make('weight_unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('calories')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('preparation_time')
                    ->label('Prep Time (min)')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListDishVariants::route('/'),
            'create' => Pages\CreateDishVariant::route('/create'),
            'edit' => Pages\EditDishVariant::route('/{record}/edit'),
        ];
    }
}
