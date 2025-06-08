<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Resources\RestaurantResource\Pages;
use App\Models\Restaurant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Class RestaurantResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RestaurantResource extends BaseResource
{
    protected static ?string $model = Restaurant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('country')
                    ->required()
                    ->maxLength(255),
                TextInput::make('city')
                    ->required()
                    ->maxLength(255),
                TextInput::make('place')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('website')
                    ->url()
                    ->maxLength(255),
                TextInput::make('location')
                    ->url()
                    ->maxLength(255),
                Select::make('timezone')
                    ->searchable()
                    ->options(\DateTimeZone::listIdentifiers())
                    ->required(),
                TextInput::make('popularity')
                    ->numeric()
                    ->nullable(),
                TextInput::make('currency')
                    ->maxLength(10),
                TextInput::make('establishment')
                    ->maxLength(255),
                TextInput::make('locale')
                    ->maxLength(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('country')->searchable(),
                Tables\Columns\TextColumn::make('city')->searchable(),
                Tables\Columns\TextColumn::make('timezone'),
                Tables\Columns\TextColumn::make('popularity')->sortable(),
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
            'index' => Pages\ListRestaurants::route('/'),
            'create' => Pages\CreateRestaurant::route('/create'),
            'edit' => Pages\EditRestaurant::route('/{record}/edit'),
        ];
    }
}
