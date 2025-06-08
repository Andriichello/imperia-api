<?php

namespace App\Filament\Resources;

use App\Enums\Hotness;
use App\Filament\BaseResource;
use App\Filament\Fields\RestaurantId;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Class ProductResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RestaurantId::make()
                    ->searchable()
                    ->required(),
                TextInput::make('slug')
                    ->maxLength(255),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->numeric()
                    ->required(),
                TextInput::make('weight')
                    ->maxLength(255),
                TextInput::make('weight_unit')
                    ->maxLength(255),
                TextInput::make('badge')
                    ->maxLength(255),
                Toggle::make('archived')
                    ->default(false),
                TextInput::make('popularity')
                    ->numeric()
                    ->nullable(),
                TextInput::make('preparation_time')
                    ->label('Preparation Time (minutes)')
                    ->numeric()
                    ->nullable(),
                TextInput::make('calories')
                    ->numeric()
                    ->nullable(),
                Toggle::make('is_vegan')
                    ->label('Vegan')
                    ->default(false),
                Toggle::make('is_vegetarian')
                    ->label('Vegetarian')
                    ->default(false),
                Toggle::make('is_low_calorie')
                    ->label('Low Calorie')
                    ->default(false),
                Toggle::make('has_eggs')
                    ->label('Contains Eggs')
                    ->default(false),
                Toggle::make('has_nuts')
                    ->label('Contains Nuts')
                    ->default(false),
                Select::make('hotness')
                    ->options(Hotness::getValues())
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
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
                Tables\Columns\IconColumn::make('is_vegan')
                    ->label('Vegan')
                    ->icon('heroicon-o-check')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_vegetarian')
                    ->label('Vegetarian')
                    ->boolean(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
